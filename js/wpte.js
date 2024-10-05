jQuery(document).ready(function ($) {
  // Function to toggle column selection
  function toggleColumnSelection($table, index, select) {
    $table.find("tr").each(function () {
      $(this)
        .find("th, td")
        .eq(index)
        .toggleClass("wpte-column-selected", select);
    });
  }

  // Function to update selected rows
  function updateSelectedRows(checkboxes, selected) {
    selected = [];
    checkboxes.filter(":checked").each(function () {
      selected.push($(this).closest("tr").index());
    });
  }

  // Function to show or hide export button based on selection
  function showExportButton(exportButton, selectedColumns, selectedRows) {
    if (selectedColumns.length > 0 && selectedRows.length > 0) {
      exportButton.show();
    } else {
      exportButton.hide();
    }
  }

  // Function to export selected rows and columns to CSV
  function exportToCSV(data) {
    const content = "data:text/csv;charset=utf-8," + data.join("\n");
    const encodedUri = encodeURI(content);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "table-export.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // Main function
  $("table.wp-list-table").each(function () {
    const $table = $(this);

    // Container
    const $cardContainer = $('<div class="postbox wpte-postbox"></div>');
    const $cardInside = $('<div class="inside wpte-inside"></div>');
    const $cardTitle = $("<span>Table Export</span>");

    // Labels
    const columnsLabel = $(
      '<span class="wpte-label">0 columns selected</span>'
    );
    const rowsLabel = $('<span class="wpte-label">0 rows selected</span>');

    // Button
    const exportButton = $(
      '<button type="button" class="button" style="display: none;">Export Selected</button>'
    );

    $cardInside.append($cardTitle);
    $cardInside.append(columnsLabel);
    $cardInside.append(rowsLabel);
    $cardInside.append(exportButton);
    $cardContainer.append($cardInside);
    $table.before($cardContainer);

    let selectedColumns = [],
      selectedRows = [];

    // Handle column header labels visibility
    columnsLabel.show();
    rowsLabel.show();

    // Handle column header click for selection
    $table.find("tr:first-child th").on("click", function () {
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
      showExportButton(exportButton, selectedColumns, selectedRows);
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
          $row.prepend(
            '<th><input type="checkbox" class="wpte-select-all-checkbox"></th>'
          );
        } else {
          $row.prepend(
            '<td><input type="checkbox" class="wpte-row-checkbox"></td>'
          );
        }
      });
      $selectAllCheckbox = $table.find(".wpte-select-all-checkbox");
      $rowCheckboxes = $table.find(".wpte-row-checkbox");
    }

    // Handle "Select All" checkbox
    $selectAllCheckbox.on("change", function () {
      const isChecked = $(this).prop("checked");
      $rowCheckboxes.prop("checked", isChecked);
      updateSelectedRows($rowCheckboxes, selectedRows);
      rowsLabel.text(selectedRows.length + " rows selected");
      showExportButton(exportButton, selectedColumns, selectedRows);
    });

    // Handle individual row checkbox changes
    $rowCheckboxes.on("change", function () {
      updateSelectedRows($rowCheckboxes, selectedRows);
      rowsLabel.text(selectedRows.length + " rows selected");
      showExportButton(exportButton, selectedColumns, selectedRows);
    });

    // Handle 'Export Selected' button click
    exportButton.on("click", function (event) {
      event.preventDefault();

      if (selectedColumns.length === 0 || selectedRows.length === 0) {
        alert("No columns or rows selected for export.");
        return;
      }

      // Gather data from selected columns and rows
      let data = [];
      $table.find("tr").each(function (rowIndex) {
        if (selectedRows.includes(rowIndex)) {
          let rowData = [];
          $(this)
            .find("th, td")
            .each(function (colIndex) {
              // Check if column is selected
              if (selectedColumns.includes(colIndex)) {
                let text = $(this).text().trim();
                rowData.push('"' + text.replace(/"/g, '""') + '"');
              }
            });
          data.push(rowData.join(","));
        }
      });

      // Export data to CSV
      exportToCSV(data);
    });
  });
});
