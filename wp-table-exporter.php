<?php
/**
 * Plugin Name: WP Table Exporter
 * Description: Adds export functionality to admin dashboard tables, allowing users to choose which rows and columns to export as CSV.
 * Version: 1.0
 * Author: Diogo Carvalho
 */

// Hook to add the export buttons and column selection functionality
add_action('admin_footer', 'wpte_add_column_selection_and_export_button');

function wpte_add_column_selection_and_export_button() {
?>
  <script>
    jQuery(document).ready(function ($) {
      $("table.wp-list-table").each(function () {
        const $table = $(this);

        // Container
        const $cardContainer = $('<div class="postbox wpte-postbox"></div>');
        const $cardInside = $('<div class="inside wpte-inside"></div>');
        const $cardTitle = $("<span>Table Export</span>");

        // Labels
        const columnsLabel = $('<span class="wpte-label"></span>');
        const rowsLabel = $('<span class="wpte-label"></span>');

        // Buttons
        const startSelectionText = "Start Selection";
        const stopSelectionText = "Stop Selection";
        const selectButton = $(`<button type="button" class="button">${startSelectionText}</button>`);
        const exportButton = $('<button type="button" class="button" style="display: none;">Export Selected</button>');

        $cardInside.append($cardTitle);
        $cardInside.append(selectButton);
        $cardInside.append(columnsLabel);
        $cardInside.append(rowsLabel);
        $cardInside.append(exportButton);
        $cardContainer.append($cardInside);
        $table.before($cardContainer);

        let selectedColumns = [], selectedRows = [];

        // Handle 'Select Columns' button click
        selectButton.on("click", function () {
          event.preventDefault();

          // Toggle selection mode
          if (!$(this).data("selection-mode")) {
            $(this).data("selection-mode", true).text(startSelectionText);
            const columnCount = selectedColumns.length;
            const rowsCount = selectedRows.length;
            columnsLabel.text(columnCount + " columns selected").show();
            rowsLabel.text(rowsCount + " rows selected").show();
            $table.find("tr:first-child th").css("cursor", "pointer");
            if (columnCount > 0 && rowsCount > 0) {
              exportButton.show();
            }
          } else {
            $(this).data("selection-mode", false).text(stopSelectionText);
            columnsLabel.text("").hide();
            rowsLabel.text("").hide();
            $table.find("tr:first-child th").css("cursor", "default");
            exportButton.hide();
          }
        });

        // Show or hide export button based on selection
        function showExportButton() {
          if (selectedColumns.length > 0 && selectedRows.length > 0) {
            exportButton.show();
          } else {
            exportButton.hide();
          }
        }

        // Function to toggle column selection
        function toggleColumnSelection($table, index, select) {
          $table.find("tr").each(function () {
            $(this)
              .find("th, td")
              .eq(index)
              .toggleClass("wpte-column-selected", select);
          });
        }

        // Handle column header click for selection
        $table.find("tr:first-child th").on("click", function () {
          if (selectButton.data("selection-mode")) {
            const columnIndex = $(this).index();
            
            // Skip the checkbox column (index 0)
            if (columnIndex === 0) {
              return;
            }

            const alreadySelected = selectedColumns.includes(columnIndex);

            if (alreadySelected) {
              // Deselect the column
              selectedColumns = selectedColumns.filter(function (index) {
                return index !== columnIndex;
              });
              toggleColumnSelection($table, columnIndex, false);
            } else {
              // Select the column
              selectedColumns.push(columnIndex);
              toggleColumnSelection($table, columnIndex, true);
            }
            columnsLabel.text(selectedColumns.length + " columns selected");

            // Show or hide export button based on selection
            showExportButton();
          }
        });

        
        // Find existing checkboxes or create new ones if needed
        let $selectAllCheckbox, $rowCheckboxes;
        
        if ($table.find('th input[type="checkbox"]').length > 0) {
          // Use existing checkboxes
          $selectAllCheckbox = $table.find('thead input[type="checkbox"]').first();
          $rowCheckboxes = $table.find('tbody input[type="checkbox"]');
        } else {
          // Create new checkboxes
          $table.find("tr").each(function (index) {
            var $row = $(this);
            if (index === 0) {
              $row.prepend('<th><input type="checkbox" class="wpte-select-all-checkbox"></th>');
            } else {
              $row.prepend('<td><input type="checkbox" class="wpte-row-checkbox"></td>');
            }
          });
          $selectAllCheckbox = $table.find('.wpte-select-all-checkbox');
          $rowCheckboxes = $table.find('.wpte-row-checkbox');
        }

        // Handle "Select All" checkbox
        $selectAllCheckbox.on('change', function() {
          var isChecked = $(this).prop('checked');
          $rowCheckboxes.prop('checked', isChecked);
          updateSelectedRows();
        });

        // Handle individual row checkbox changes
        $rowCheckboxes.on('change', function() {
          updateSelectedRows();
        });

        // Function to update selected rows
        function updateSelectedRows() {
          selectedRows = [];
          $rowCheckboxes.filter(':checked').each(function() {
            selectedRows.push($(this).closest('tr').index());
          });
          rowsLabel.text(selectedRows.length + " rows selected");
          showExportButton();
        }

        // Remove any existing row click handlers
        $table.find("tr").off("click");

        // Handle 'Export Selected' button click
        exportButton.on("click", function () {
          event.preventDefault();

          if (selectedColumns.length === 0 || selectedRows.length === 0) {
            alert("No columns or rows selected for export.");
            return;
          }

          let csv = [];

          // Gather data from selected columns and rows
          $table.find("tr").each(function (rowIndex) {
            if (selectedRows.length === 0 || selectedRows.includes(rowIndex)) {
              let data = [];
              $(this)
                .find("th, td")
                .each(function (colIndex) {
                  // Skip the checkbox column (index 0)
                  if (colIndex > 0 && selectedColumns.includes(colIndex)) {
                    let text = $(this).text().trim();
                    data.push('"' + text.replace(/"/g, '""') + '"');
                  }
                });
              csv.push(data.join(","));
            }
          });

          const csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
          const encodedUri = encodeURI(csvContent);
          const link = document.createElement("a");
          link.setAttribute("href", encodedUri);
          link.setAttribute("download", "table-export.csv");
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        });
      });
    });
  </script>
  <style>
    .wpte-postbox {
      margin: 15px 0 !important;
    }

    .wpte-inside {
      padding: 0 12px !important;
      display: flex;
      align-items: center;
      column-gap: .5rem;
    }

    .button.wpte-label {
      margin: 5px;
      display: inline-block;
    }

    .wpte-column-selected {
      background-color: #d9edf7 !important; /* Light blue background */
    }
  </style>
<?php
}
