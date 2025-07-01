var jqxhr = { abort: function () {} };
(function ($) {
  "use strict";
  $.fn.gridTable = function (options, callback) {
    var settings = $.extend(
      {
        width: "100%",
        columns: [],
        widthColumns: [],
        classColumns: [],
        rows: [],
        focusAfterAdd: 0,
        allowNewRow: true,
        deleteAction: false,
        addAction: false,
      },
      options
    );

    var lastRow = 0;

    var gridTable = {
      addRow: function () {
        return addRow();
      },
      deleteRow: function (row) {
        return deleteRow(row);
      },
      getLastRow: function () {
        return lastRow - 1;
      },
      getValCell: function (fieldName, krow) {
        return getValCell(fieldName, krow);
      },
      getValCellSelect: function (fieldName, krow) {
        return getValCellSelect(fieldName, krow);
      },
      setValCell: function (fieldName, krow, value) {
        setValCell(fieldName, krow, value);
      },
      setValCellSelect: function (fieldName, krow, value) {
        return setValCellSelect(fieldName, krow, value);
      },
      destroy: function () {
        destroy();
      },
    };

    var tableId = $(this).attr("id");

    $(this).addClass("table table-sm gridtable");

    var columnsLength = settings.columns.length;

    var autocompleteArr = [];

    // initial header
    var header = "<thead><tr>";
    if (settings.deleteAction) {
      header += "<th class='text-center' width='30'>#</th>";
    }
    for (var i = 0; i <= settings.columns.length - 1; i++) {
      if (settings.columns[i].type != "hidden") {
        header =
          header +
          "<th width='" +
          settings.columns[i].width +
          "' class='text-center align-middle'>" +
          settings.columns[i].name +
          "</th>";
      } else {
        columnsLength = columnsLength - 1;
      }
    }
    header = header + "</tr></thead>";
    this.append(header);

    //
    function gridTableDelay(callback, ms) {
      var timer = 0;
      return function () {
        var context = this,
          args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
          callback.apply(context, args);
        }, ms || 0);
      };
    }

    //getter and setter
    function getValCell(fieldName, krow) {
      var valCell = $(
        "#" +
          tableId +
          ' input[name="' +
          fieldName +
          '[]"][data-row="' +
          krow +
          '"]'
      ).val();

      if (isInt(numClear(valCell)) || isFloat(numClear(valCell))) {
        return numClear(valCell);
      } else {
        return valCell;
      }
    }

    function getValCellSelect(fieldName, krow) {
      var valCell = $(
        "#" +
          tableId +
          ' select[name="' +
          fieldName +
          '[]"][data-row="' +
          krow +
          '"]'
      ).val();

      return valCell;
    }

    function setValCell(fieldName, krow, value) {
      $(
        "#" +
          tableId +
          ' input[name="' +
          fieldName +
          '[]"][data-row="' +
          krow +
          '"]'
      ).val(value);
    }

    function setValCellSelect(fieldName, krow, value) {
      $(
        "#" +
          tableId +
          ' select[name="' +
          fieldName +
          '[]"][data-row="' +
          krow +
          '"]'
      ).val(value).trigger('change');
    }

    //destroy
    function destroy() {
      $("#" + tableId).html("");
      lastRow = 0;
    }

    //initial rows
    var bodyHtml = $('<tbody id="' + tableId + '_gridtable_body"></tbody>');
    this.append(bodyHtml);
    if (settings.rows.length > 0) {
      settings.rows.forEach((row, krow) => {
        addRow(row, krow);
      });
      // Start Add by Noyr
      // Ganti baris langsung eksekusi function, tambahkan baris kosong setelah eksekusi fungsi
      if (settings.addAfterEnter) {
        addRow();
      }
      // End Add by Noyr
    } else {
      addRow();
    }

    function addRow(arr = [], rowIndex = null) {
      var krow = lastRow;
      // var krow = rowIndex;
      // if (rowIndex == null) {
      //   krow = settings.rows.length;
      // }
      var rowHtml = $(
        '<tr id="' +
          tableId +
          "_gridtable_row_" +
          krow +
          '" data-row="' +
          krow +
          '"></tr>'
      );
      var rowsArray = [];
      if (settings.deleteAction) {
        var cellHtml = $(
          "<td data-row='" + krow + "' class='text-center'></td>"
        );
        var cellContentHtml = $(
          "<a href='javascript:void(0)' id='" +
            tableId +
            "gridtable_btn_" +
            krow +
            "'><i class='fas fa-trash-alt text-danger' style='font-size:0.875rem;margin-top:0.3rem'> </i></a>"
        );
        cellHtml.append(cellContentHtml);
        rowHtml.append(cellHtml);
        //
        cellContentHtml.click(function (e) {
          if (settings.deleteBefore) {
            deleteOnBefore(krow);
          } else {
            deleteRow(krow);
          }
        });
      }
      for (let i = 0; i < settings.columns.length; i++) {
        var value = "";
        if (arr[i] != undefined) {
          value = arr[i];
        }
        if (typeof settings.columns[i].fieldName === "undefined") {
          var fieldName = "";
        } else {
          var fieldName = settings.columns[i].fieldName;
        }
        if (typeof settings.columns[i].bodyClass === "undefined") {
          var className = "";
        } else {
          var className = settings.columns[i].bodyClass;
        }
        if (typeof settings.columns[i].keyUp === "undefined") {
          var keyUp = "";
        } else {
          var keyUp = settings.columns[i].keyUp;
        }
        if (typeof settings.columns[i].keyDown === "undefined") {
          var keyDown = "";
        } else {
          var keyDown = settings.columns[i].keyDown;
        }
        if (typeof settings.columns[i].keyPress === "undefined") {
          var keyPress = "";
        } else {
          var keyPress = settings.columns[i].keyPress;
        }
        if (typeof settings.columns[i].focus === "undefined") {
          var focus = "";
        } else {
          var focus = settings.columns[i].focus;
        }
        if (typeof settings.columns[i].required === "undefined") {
          var required = "";
        } else {
          var required = settings.columns[i].required;
        }
        if (value == "") {
          if (typeof settings.columns[i].defaultValue !== "undefined") {
            value = settings.columns[i].defaultValue;
          }
          if (typeof settings.columns[i].autoNumeric !== "undefined") {
            if (settings.columns[i].autoNumeric == true) {
              value = krow + 1;
            }
          }
        }

        if (settings.columns[i].type != "hidden") {
          var cellHtml = $("<td></td>");

          if (settings.columns[i].type == "date") {
            // edit by noyr -> add input type date
            var cellContentHtml = $(
              '<input type="date" class="grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '">'
            );
          } else if (settings.columns[i].type == "number") {
            var cellContentHtml = $(
              '<input type="text" class="grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '" pattern="[0-9]+(\\.[0-9]*?)?">'
            );
          } else if (settings.columns[i].type == "float") {
            // edit by noyr -> add input type float
            var cellContentHtml = $(
              '<input type="number" class="grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '" step=any min=0>'
            );
          } else if (settings.columns[i].type == "checkbox") {
            // edit by noyr -> add input type checkbox
            var checked = value == 1 ? "checked" : "";
            if (value == "" || value == null) {
              value = 0;
            }
            var cellContentHtml = $(
              '<input type="checkbox" class="grid-input mt-1 mb-1 w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '" ' +
                checked +
                ">"
            );
          } else if (settings.columns[i].type == "checkbox_multiple") {
            var checked = value == 1 ? "checked" : "";
            if (value == "" || value == null) {
              value = 0;
            }
            var cellContentHtml = $(
              '<div class="' +
                className +
                '" id="label_' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" data-row="' +
                krow +
                '" data-col="' +
                i +
                '" data-tableid="' +
                tableId +
                '" data-name="' +
                fieldName +
                '">' +
                "</div>"
            );
          } else if (settings.columns[i].type == "textarea") {
            if (typeof settings.columns[i].rowsHeight === "undefined") {
              var rowsHeight = "7";
            } else {
              var rowsHeight = settings.columns[i].rowsHeight;
            }
            var cellContentHtml = $(
              '<textarea class="grid-input w-100 ' +
                className +
                '" rows="' +
                rowsHeight +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '">' +
                value +
                "</textarea>"
            );
            // var textarea_val = value;
            // var tid = setInterval( function () {
            //   if (document.readyState !== 'complete' ) return;
            //   clearInterval(tid);
            //   $('#'+tableId+'_gridtable_cell_'+krow+'_'+i).val(textarea_val);
            // }, 100 );
          } else if (settings.columns[i].type == "select") {
            var cellContentHtml = $(
              '<select class="form-select form-select-grid grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '">' +
                "</select>"
            );
          } else if (settings.columns[i].type == "select2") {
            var cellContentHtml = $(
              '<select class="form-select chosen-select-grid grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '">' +
                "</select>"
            );
            var tid = setInterval(function () {
              if (document.readyState !== "complete") return;
              clearInterval(tid);
              $(".chosen-select-grid").each(function () {
                $(this).select2({
                  theme: "bootstrap-5",
                  dropdownParent: $(this).parent(),
                  width: $(this).data("width")
                    ? $(this).data("width")
                    : $(this).hasClass("w-100")
                    ? "100%"
                    : "style",
                });
              });
            }, 100);
          } else if (settings.columns[i].type == "select2_multiple") {
            var cellContentHtml = $(
              '<select class="form-select chosen-select-grid grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                "_" +
                krow +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '" multiple="multiple">' +
                "</select>"
            );
            var tid = setInterval(function () {
              if (document.readyState !== "complete") return;
              clearInterval(tid);
              $(".chosen-select-grid").each(function () {
                $(this).select2({
                  theme: "classic",
                  dropdownParent: $(this).parent(),
                  width: $(this).data("width")
                    ? $(this).data("width")
                    : $(this).hasClass("w-100")
                    ? "100%"
                    : "style",
                });
                // .on('change', function(e) {
                //   if($(this).val() && $(this).val().length) {
                //     $(this).next('.select2-container')
                //       .find('li.select2-search--inline input.select2-search__field').attr('placeholder', 'Select items');
                //   }
                // });
              });
            }, 100);
          } else if (settings.columns[i].type == "button") {
            var hrefId = "";
            if (settings.columns[i].buttonMultipleHrefId !== undefined) {
              settings.columns[i].buttonMultipleHrefId.forEach((v, k) => {
                // console.log(v);
                setTimeout(function () {
                  hrefId +=
                    "/" +
                    $(
                      'input[name="' + v + '[]"][data-row="' + krow + '"]'
                    ).val();
                }, 500);
              });
            }

            if (settings.columns[i].buttonType == "href") {
              if (settings.columns[i].buttonTarget == "modal") {
                var cellContentHtml = $(
                  '<a class="mt-1 mb-1 ' +
                    className +
                    '" id="' +
                    tableId +
                    "_gridtable_cell_" +
                    krow +
                    "_" +
                    i +
                    '" data-row="' +
                    krow +
                    '" data-col="' +
                    i +
                    '" ' +
                    '" onclick="_modalPrint(event, {uri: \'' +
                    settings.columns[i].buttonHref +
                    "', size: 'modal-xl', position: 'normal', title: '" +
                    settings.columns[i].buttonModalTitle +
                    "'})\" " +
                    ">" +
                    settings.columns[i].buttonTitle +
                    "</a>"
                );
                setTimeout(function () {
                  $("#" + tableId + "_gridtable_cell_" + krow + "_" + i).attr(
                    "onclick",
                    "_modalPrint(event, {uri: '" +
                      settings.columns[i].buttonHref +
                      hrefId +
                      "', size: 'modal-xl', position: 'normal', title: '" +
                      settings.columns[i].buttonModalTitle +
                      "'})"
                  );
                }, 500);
              }
              if (settings.columns[i].buttonTarget == "modal2") {
                var cellContentHtml = $(
                  '<a class="mt-1 mb-1 ' +
                    className +
                    '" id="' +
                    tableId +
                    "_gridtable_cell_" +
                    krow +
                    "_" +
                    i +
                    '" data-row="' +
                    krow +
                    '" data-col="' +
                    i +
                    '" ' +
                    '" onclick="_modal(event, {uri: \'' +
                    settings.columns[i].buttonHref +
                    "', size: '" +
                    settings.columns[i].buttonModalSize +
                    "', position: 'normal', title: '" +
                    settings.columns[i].buttonModalTitle +
                    "'})\" " +
                    ">" +
                    settings.columns[i].buttonTitle +
                    "</a>"
                );
                setTimeout(function () {
                  $("#" + tableId + "_gridtable_cell_" + krow + "_" + i).attr(
                    "onclick",
                    "_modal(event, {uri: '" +
                      settings.columns[i].buttonHref +
                      hrefId +
                      "', size: '" +
                      settings.columns[i].buttonModalSize +
                      "', position: 'normal', title: '" +
                      settings.columns[i].buttonModalTitle +
                      "'})"
                  );
                }, 500);
              }
            }
          } else {
            var cellContentHtml = $(
              '<input class="grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" onkeyup="' +
                keyUp +
                '" onkeydown="' +
                keyDown +
                '" onkeypress="' +
                keyPress +
                '" onfocus="' +
                focus +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '" readonly>'
            );
          }

          if (required) {
            $(cellContentHtml).attr("required", true);
          }

          additionalElement(cellContentHtml);

          if (value != "") {
            $(cellContentHtml).trigger("change");
          }
          cellHtml.append(cellContentHtml);
          rowHtml.append(cellHtml);
          cellContentHtml.keyup(function (e) {
            onkeyup(this);
          });
          cellContentHtml.keydown(function (e) {
            onkeydown(this, e);
          });
          if (settings.columns[i].type == "text") {
            cellContentHtml.change(function (e) {
              onchange(i, this);
            });
          }
          if (settings.columns[i].type == "number") {
            cellContentHtml.change(function (e) {
              onchange(i, this);
            });
          }
          if (settings.columns[i].type == "float") {
            cellContentHtml.change(function (e) {
              onchange(i, this);
            });
          }
          if (settings.columns[i].type == "checkbox") {
            cellContentHtml.change(function (e) {
              onchange(i, this);
            });
          }
          if (settings.columns[i].type == "autocomplete") {
            gridcomplete(cellContentHtml, i);
          }
          if (settings.columns[i].type == "select") {
            selectcomplete(cellContentHtml, i, value);
            cellContentHtml.change(function (e) {
              onchange(i, this);
            });
          }
          if (settings.columns[i].type == "select2") {
            selectcomplete(cellContentHtml, i, value);
            cellContentHtml.change(function (e) {
              onchange(i, this);
            });
          }
          if (settings.columns[i].type == "select2_multiple") {
            selectMultiplecomplete(cellContentHtml, i, value);
            cellContentHtml.change(function (e) {
              onchange(i, this);
            });
          }
          if (settings.columns[i].type == "checkbox_multiple") {
            checkboxmultiple(cellContentHtml, i, value);
            cellContentHtml.change(function (e) {
              onchange(i, this);
            });
          }

          rowsArray.push(value);
        } else {
          setTimeout(function () {
            if (typeof settings.columns[i].fieldName === "undefined") {
              var fieldName = "";
            } else {
              var fieldName = settings.columns[i].fieldName;
            }
            var value = "";
            if (arr[i] != undefined) {
              value = arr[i];
            }
            if (value == "") {
              if (typeof settings.columns[i].defaultValue !== "undefined") {
                value = settings.columns[i].defaultValue;
              }
            }
            var cellContentHtmlHidden = $(
              '<input type="hidden" class="grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                i +
                '" name="' +
                fieldName +
                '[]" data-row="' +
                krow +
                '" data-col="' +
                i +
                '" value="' +
                value +
                '">'
            );
            $("#" + tableId + "_gridtable_cell_" + krow + "_0").after(
              cellContentHtmlHidden
            );
            // additionalElement(cellContentHtmlHidden);
          }, 100);
        }
      }
      if (arr.length === 0) {
        settings.rows.push(rowsArray);
      }
      bodyHtml.append(rowHtml);
      $(
        "#" + tableId + "_gridtable_cell_" + krow + "_" + settings.focusAfterAdd
      ).focus();

      //
      if (settings.rows[0].length > settings.columns.length) {
        var val = "";
        for (
          let j = settings.columns.length;
          j < settings.rows[0].length;
          j++
        ) {
          setTimeout(function () {
            val = "";
            if (typeof arr[j] !== "undefined") {
              val = arr[j];
            }
            var cellContentHtmlHidden = $(
              '<input type="hidden" class="grid-input w-100 ' +
                className +
                '" id="' +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                j +
                '" name="' +
                tableId +
                "_gridtable_cell_" +
                j +
                '[]" data-row="' +
                krow +
                '" data-col="' +
                j +
                '" value="' +
                val +
                '">'
            );
            $(
              "#" +
                tableId +
                "_gridtable_cell_" +
                krow +
                "_" +
                (settings.columns.length - 1)
            ).after(cellContentHtmlHidden);
            additionalElement(cellContentHtml);
          }, 100);
        }
      }
      //

      lastRow += 1;

      return krow;
    }

    function additionalElement(obj) {
      if ($(obj).hasClass("autonumeric")) {
        $(obj).autoNumeric({
          aSep: ".",
          aDec: ",",
          vMax: "999999999999999",
          vMin: "0",
        });
      }
      if ($(obj).hasClass("autonumeric-float")) {
        $(obj).autoNumeric({
          aSep: ".",
          aDec: ",",
          aForm: true,
          vMax: "999999999999999999.99",
          vMin: "-999999999999999999.99",
          mDec: "2",
          aPad: false,
        });
        $(obj).on("change", function () {
          var value = numClear($(obj).val());
          $(obj).val(numId(value, true));
        });
      }
      if ($(obj).hasClass("autonumeric-float-long")) {
        $(obj).autoNumeric({
          aSep: ".",
          aDec: ",",
          aForm: true,
          vMax: "999999.9999",
          vMin: "-999999.9999",
          mDec: "4",
          aPad: false,
        });
        $(obj).on("change", function () {
          var value = numClear($(obj).val());
          $(obj).val(numId(value, true, 4));
        });
      }
      if ($(obj).hasClass("autonumeric-float-readonly")) {
        $(obj).on("input", function () {
          var value = numClear($(obj).val());
          $(obj).val(numId(value, true));
        });
      }

      if ($(obj).hasClass("datepicker")) {
        $(obj).daterangepicker(
          {
            singleDatePicker: true,
            showDropdowns: true,
            timePicker: false,
            timePicker24Hour: false,
            autoApply: true,
            locale: {
              format: "DD-MM-YYYY",
              separator: " - ",
              applyLabel: "&nbsp; Pilih &nbsp;",
              cancelLabel: "&nbsp; Batal &nbsp;",
              fromLabel: "From",
              toLabel: "To",
              customRangeLabel: "Custom",
              weekLabel: "W",
              daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
              monthNames: [
                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember",
              ],
              firstDay: 1,
            },
            drops: "auto",
          },
          function (start, end, label) {
            // code if apply
          }
        );
      }

      if ($(obj).hasClass("datetimepicker")) {
        $(obj).daterangepicker(
          {
            singleDatePicker: true,
            showDropdowns: true,
            timePicker: true,
            timePicker24Hour: true,
            autoApply: true,
            locale: {
              format: "DD-MM-YYYY HH:mm:ss",
              separator: " - ",
              applyLabel: "&nbsp; Pilih &nbsp;",
              cancelLabel: "&nbsp; Batal &nbsp;",
              fromLabel: "From",
              toLabel: "To",
              customRangeLabel: "Custom",
              weekLabel: "W",
              daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
              monthNames: [
                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember",
              ],
              firstDay: 1,
            },
            drops: "auto",
          },
          function (start, end, label) {
            // code if apply
          }
        );
      }

      if ($(obj).hasClass("datepicker-notauto")) {
        $(obj).daterangepicker(
          {
            singleDatePicker: true,
            showDropdowns: true,
            timePicker: false,
            timePicker24Hour: false,
            autoApply: false,
            autoUpdateInput: false,
            locale: {
              format: "DD-MM-YYYY",
              separator: " - ",
              applyLabel: "&nbsp; Pilih &nbsp;",
              cancelLabel: "&nbsp; Batal &nbsp;",
              fromLabel: "From",
              toLabel: "To",
              customRangeLabel: "Custom",
              weekLabel: "W",
              daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
              monthNames: [
                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember",
              ],
              firstDay: 1,
            },
            drops: "auto",
          },
          function (start, end, label) {
            // code if apply
          }
        );
        $(obj).on("apply.daterangepicker", function (ev, picker) {
          $(this).val(picker.startDate.format("DD-MM-YYYY"));
        });

        $(obj).on("cancel.daterangepicker", function (ev, picker) {
          $(this).val("");
        });
      }
    }

    function deleteOnBefore(row) {
      settings.deleteOnBefore.call({
        row: row,
      });
    }

    function addAfterEnter(row) {
      settings.addAfterEnter.call({
        row: row,
      });
    }

    function deleteRow(row) {
      console.log(row);
      console.log(lastRow);

      var obj = $("#" + tableId + "_gridtable_row_" + row);
      if (settings.rows.length > 1) {
        if ($.isFunction(settings.deleteOnChange)) {
          setTimeout(() => {
            settings.deleteOnChange.call();
          }, 100);
        }
        $(obj).remove();
        if (row == lastRow - 1) {
          lastRow--;
        }
      }
    }

    // function deleterow(obj, e) {
    //   let krow = $(obj).data("row");
    //   // add by noyr
    //   // delete row if row more than 1
    //   if (settings.rows.length > 1) {
    //     if ($.isFunction(settings.deleteOnChange)) {
    //       setTimeout(() => {
    //         settings.deleteOnChange.call();
    //       }, 100);
    //     }
    //     // settings.rows.splice(krow, 1); // bug tidak bisa tambah baris baru
    //     $(obj).remove();
    //   }
    //   // end by noyr
    //   // settings.rows.splice(krow,1);
    //   // $(obj).remove();
    // }

    function onchange(i, obj) {
      if ($.isFunction(settings.columns[i].onchange)) {
        settings.columns[i].onchange.call(obj);
      }
      // setTimeout(() => {
      //   additionalElement();
      // }, 100);
    }

    function onkeyup(obj) {
      var krow = $(obj).data("col");
    }

    function onkeydown(obj, e) {
      var krow = $(obj).data("row");
      var kcol = $(obj).data("col");
      if (e.which == 13) {
        if (settings.columns[kcol].allowNewRow === undefined) {
          var allowNewRow = true;
        } else {
          var allowNewRow = settings.columns[kcol].allowNewRow;
        }
        if (allowNewRow == true) {
          e.preventDefault();
        }
        if (kcol === columnsLength - 1 && krow === settings.rows.length - 1) {
          if (allowNewRow == true) {
            if (settings.allowNewRow) {
              // alert(
              //   "Kcol =" +
              //     kcol +
              //     "columnsLength =" +
              //     columnsLength +
              //     " settings.rows.length =" +
              //     settings.rows.length
              // );

              // Start Add by Noyr
              // Ganti baris langsung eksekusi function
              if (settings.addAfterEnter) {
                addAfterEnter(krow);
              } else {
                addRow();
              }
              // End Add by Noyr
            }
          }
        } else {
          if (allowNewRow == true) {
            if (settings.columns[kcol].newRow === undefined) {
              var nextcol = kcol + 1;
              if (kcol === columnsLength - 1) {
                nextcol = 0;
                krow += 1;
              }
              if (settings.columns[kcol].nextCol !== undefined) {
                nextcol = settings.columns[kcol].nextCol;
              }
            } else {
              // Start Add by Noyr
              // Ganti baris langsung eksekusi function
              if (settings.addAfterEnter) {
                addAfterEnter(krow);
              } else {
                addRow();
              }
              // End Add by Noyr
            }

            setTimeout(() => {
              $("#" + tableId + "_gridtable_cell_" + krow + "_" + nextcol)
                .focus()
                .select();
            }, 100);
          }
        }
        if (allowNewRow == true) {
          $("#" + tableId + "_gridtable_cell_" + krow + "_" + kcol).prop(
            "readonly",
            true
          );
        }
      } else {
        if (e.which == 46) {
          let row = $(obj).data("row");
          let objRow = $("#" + tableId + "_gridtable_row_" + row);
          let nextRow = document.getElementById(
            tableId + "_gridtable_row_" + (row + 1)
          );
          if (nextRow == null) {
            arrowUp(obj, e);
          } else {
            arrowDown(obj, e);
          }
          // disabled by untung sukasno
          // deleteRow(row);
        }
        if (e.which != 37 && e.which != 38 && e.which != 39 && e.which != 40) {
          if (
            $("#" + tableId + "_gridtable_cell_" + krow + "_" + kcol).prop(
              "readonly"
            )
          ) {
            // Check input type "readonly"
            if (settings.columns[kcol].type == "readonly") {
              $("#" + tableId + "_gridtable_cell_" + krow + "_" + kcol)
                .attr("readonly", true)
                .select();
            } else {
              $("#" + tableId + "_gridtable_cell_" + krow + "_" + kcol)
                .attr("readonly", false)
                .select();
            }
          }
        } else {
          if (
            $("#" + tableId + "_gridtable_cell_" + krow + "_" + kcol).prop(
              "readonly"
            )
          ) {
            if (e.which == 38) {
              arrowUp(obj, e);
            }
            if (e.which == 39) {
              arrowRight(obj, e);
            }
            if (e.which == 40) {
              arrowDown(obj, e);
            }
            if (e.which == 37) {
              arrowLeft(obj, e);
            }
          }
        }
      }
    }

    function arrowUp(obj, e) {
      e.preventDefault();
      var krow = parseInt($(obj).data("row")) - 1;
      var kcol = parseInt($(obj).data("col"));
      document
        .querySelector(
          'input[data-row="' + krow + '"][data-col="' + kcol + '"]'
        )
        .focus();
    }

    function arrowRight(obj, e) {
      e.preventDefault();
      var krow = parseInt($(obj).data("row"));
      var kcol = parseInt($(obj).data("col")) + 1;
      document
        .querySelector(
          'input[data-row="' + krow + '"][data-col="' + kcol + '"]'
        )
        .focus();
    }

    function arrowDown(obj, e) {
      e.preventDefault();
      var krow = parseInt($(obj).data("row")) + 1;
      var kcol = parseInt($(obj).data("col"));
      document
        .querySelector(
          'input[data-row="' + krow + '"][data-col="' + kcol + '"]'
        )
        .focus();
    }

    function arrowLeft(obj, e) {
      e.preventDefault();
      var krow = parseInt($(obj).data("row"));
      var kcol = parseInt($(obj).data("col")) - 1;
      document
        .querySelector(
          'input[data-row="' + krow + '"][data-col="' + kcol + '"]'
        )
        .focus();
    }

    function gridcomplete(obj, index) {
      if (settings.columns[index].autocomplete.multipleColumn !== undefined) {
        settings.columns[index].autocomplete.multipleColumn.forEach((v, k) => {
          $(obj).data("val" + k, "0");
        });
      }
      var selectedOnFocus = false;
      var onFocus = "";
      if (settings.columns[index].autocomplete.selectedOnFocus === undefined) {
        selectedOnFocus = false;
        onFocus = "";
      } else {
        if (settings.columns[index].autocomplete.selectedOnFocus == false) {
          selectedOnFocus =
            settings.columns[index].autocomplete.selectedOnFocus;
          onFocus = "";
        } else {
          selectedOnFocus =
            settings.columns[index].autocomplete.selectedOnFocus;
          onFocus = "focus";
        }
      }
      obj.on(
        "input " + onFocus,
        gridTableDelay(function (e) {
          var currentFocus;
          var a,
            b,
            i,
            val = this.value;
          jqxhr.abort();
          /*close any already open lists of autocompleted values*/
          closeAllLists();
          if (selectedOnFocus == false) {
            if (!val) {
              return false;
            }
          }
          currentFocus = -1;
          /*create a DIV element that will contain the items (values):*/
          a = document.createElement("DIV");
          a.setAttribute("id", this.id + "_gridcomplete-list");
          a.setAttribute("class", "gridcomplete-items");
          /*append the DIV element as a child of the autocomplete container:*/
          this.parentNode.appendChild(a);

          if (settings.columns[index].autocomplete.remoteType == "static") {
            //populate item
            var x = document.createElement("DIV");
            x.classList.add("gridcomplete-items-container");
            if (settings.columns[index].autocomplete.type == "single") {
              for (
                i = 0;
                i < settings.columns[index].autocomplete.data.length;
                i++
              ) {
                if (
                  settings.columns[index].autocomplete.data[i][1]
                    .substr(0, val.length)
                    .toUpperCase() == val.toUpperCase()
                ) {
                  b = document.createElement("DIV");
                  b.classList.add("gridcomplete-item");
                  /*make the matching letters bold:*/
                  b.innerHTML =
                    "<strong>" +
                    settings.columns[index].autocomplete.data[i][1].substr(
                      0,
                      val.length
                    ) +
                    "</strong>";
                  b.innerHTML += settings.columns[index].autocomplete.data[
                    i
                  ][1].substr(val.length);
                  /*insert a input field that will hold the current array item's value:*/
                  b.innerHTML +=
                    "<input type='hidden' value='" +
                    settings.columns[index].autocomplete.data[i][1] +
                    "'>";
                  b.addEventListener("click", function (e) {
                    /*insert the value for the autocomplete text field:*/
                    $(obj).val(this.getElementsByTagName("input")[0].value);
                    /*close the list of autocompleted values,
                          (or any other open lists of autocompleted values:*/
                    if ($.isFunction(settings.columns[index].onchange)) {
                      settings.columns[index].onchange.call(obj);
                    }
                    closeAllLists();
                  });
                  x.appendChild(b);
                }
              }
              a.appendChild(x);
            }

            if (settings.columns[index].autocomplete.type == "multiple") {
              b = document.createElement("DIV");
              b.setAttribute("class", "gridcomplete-table-container");
              var c = document.createElement("table");
              c.style.width =
                settings.columns[index].autocomplete.multipleWidth;
              c.classList.add("table");
              c.classList.add("table-bordered");
              c.classList.add("table-sm");
              var d = document.createElement("thead");
              var e = document.createElement("tr");
              var f = "";
              settings.columns[index].autocomplete.multipleColumn.forEach(
                (autocol, autokey) => {
                  f += "<th>" + autocol + "</th>";
                }
              );
              e.innerHTML = f;
              d.appendChild(e);
              c.appendChild(d);
              b.appendChild(c);
              a.appendChild(b);
              // row table
              var g = document.createElement("tbody");
              var i = [];
              settings.columns[index].autocomplete.data.forEach(
                (rowVal, rowKey) => {
                  if (
                    rowVal[1].substr(0, val.length).toUpperCase() ==
                    val.toUpperCase()
                  ) {
                    var tr = document.createElement("tr");
                    tr.classList.add("gridcomplete-table-row");
                    var j = "";
                    rowVal.forEach((colVal, colKey) => {
                      j +=
                        "<td>" +
                        "<strong>" +
                        colVal.substr(0, val.length) +
                        "</strong>" +
                        colVal.substr(val.length) +
                        "</td>";
                    });
                    tr.innerHTML = j;
                    rowVal.forEach((colVal, colKey) => {
                      tr.innerHTML +=
                        "<input type='hidden' value='" + colVal + "'>";
                    });
                    tr.addEventListener("click", function (e) {
                      // /*insert the value for the autocomplete text field:*/
                      $(obj).val(
                        this.getElementsByTagName("input")[
                          settings.columns[index].autocomplete.selectedIndex
                        ].value
                      );
                      settings.columns[
                        index
                      ].autocomplete.multipleColumn.forEach((v, k) => {
                        $(obj).data(
                          "val" + k,
                          this.getElementsByTagName("input")[k].value
                        );
                      });
                      if ($.isFunction(settings.columns[index].onchange)) {
                        settings.columns[index].onchange.call(obj);
                      }
                      // /*close the list of autocompleted values,
                      //         (or any other open lists of autocompleted values:*/
                      closeAllLists();
                    });
                    i.push(tr);
                  }
                }
              );
              i.forEach((el) => {
                g.appendChild(el);
              });
              c.appendChild(g);
              b.appendChild(c);
              a.appendChild(b);
            }
          }

          if (settings.columns[index].autocomplete.remoteType == "ajax") {
            var x = document.createElement("DIV");
            x.classList.add("gridcomplete-items-container");
            if (settings.columns[index].autocomplete.type == "single") {
              // add by alfian
              if (
                typeof settings.columns[index].autocomplete.ajaxData ===
                "undefined"
              ) {
                var postData = [{ name: "term", value: val }];
              } else {
                var postData = [{ name: "term", value: val }];
                for (var key of Object.keys(
                  settings.columns[index].autocomplete.ajaxData
                )) {
                  postData.push({
                    name: key,
                    value: settings.columns[index].autocomplete.ajaxData[key],
                  });
                }
              }
              if (
                typeof settings.columns[index].autocomplete
                  .ajaxPostCustomField !== "undefined"
              ) {
                var cell = $(obj).data();
                settings.columns[
                  index
                ].autocomplete.ajaxPostCustomField.forEach(
                  (autocol, autokey) => {
                    postData.push({
                      name: "customfield_" + autocol,
                      value: $(
                        "#" +
                          tableId +
                          "_gridtable_cell_" +
                          cell.row +
                          "_" +
                          autocol
                      ).val(),
                    });
                  }
                );
              }
              // end add by alfian
              jqxhr.abort();
              console.log(settings.columns[index].autocomplete.ajaxData);
              jqxhr = $.ajax({
                type: "post",
                url: settings.columns[index].autocomplete.ajaxUrl,
                dataType: "json",
                // data: "term=" + val,
                data: postData,
                success: function (data) {
                  if (data != null) {
                    data.forEach((item, key) => {
                      b = document.createElement("DIV");
                      b.classList.add("gridcomplete-item");
                      /*make the matching letters bold:*/
                      b.innerHTML =
                        "<strong>" +
                        item[1].substr(0, val.length) +
                        "</strong>";
                      b.innerHTML += item[1].substr(val.length);
                      /*insert a input field that will hold the current array item's value:*/
                      b.innerHTML +=
                        "<input type='hidden' value='" + item[1] + "'>";
                      b.addEventListener("click", function (e) {
                        /*insert the value for the autocomplete text field:*/
                        $(obj).val(this.getElementsByTagName("input")[0].value);
                        // add by alfian
                        item.forEach(function (itemsss, key) {
                          $(obj).data("val" + key, itemsss);
                        });
                        // end add by alfian
                        /*close the list of autocompleted values,
                          (or any other open lists of autocompleted values:*/
                        if ($.isFunction(settings.columns[index].onchange)) {
                          settings.columns[index].onchange.call(obj);
                        }
                        closeAllLists();
                      });
                      x.appendChild(b);
                    });
                    a.appendChild(x);
                  }
                },
              });
            }
            if (settings.columns[index].autocomplete.type == "multiple") {
              // add by alfian
              if (
                typeof settings.columns[index].autocomplete.ajaxData ===
                "undefined"
              ) {
                var postData = [{ name: "term", value: val }];
              } else {
                var postData = [{ name: "term", value: val }];
                for (var key of Object.keys(
                  settings.columns[index].autocomplete.ajaxData
                )) {
                  // @add by alfian
                  if (
                    key == settings.columns[index].autocomplete.ajaxData[key]
                  ) {
                    postData.push({
                      name: key,
                      value: $(
                        '[name="' +
                          settings.columns[index].autocomplete.ajaxData[key] +
                          '[]"][data-row="' +
                          $(obj).data("row") +
                          '"]'
                      ).val(),
                    });
                  } else {
                    postData.push({
                      name: key,
                      value: settings.columns[index].autocomplete.ajaxData[key],
                    });
                  }
                  // @end add by alfian
                }
              }
              // end add by alfian
              jqxhr.abort();
              // console.log($(obj).data("row"));
              // console.log($('[name="kelompoktarif_id[]"][data-row="' + $(obj).data("row") + '"]').val());
              // console.log(settings.columns[index].autocomplete.ajaxData);
              jqxhr = $.ajax({
                type: "post",
                url: settings.columns[index].autocomplete.ajaxUrl,
                dataType: "json",
                // data: "term=" + val,
                data: postData,
                success: function (data) {
                  b = document.createElement("DIV");
                  b.setAttribute("class", "gridcomplete-table-container");
                  var c = document.createElement("table");
                  c.style.width =
                    settings.columns[index].autocomplete.multipleWidth;
                  c.classList.add("table");
                  c.classList.add("table-bordered");
                  c.classList.add("table-sm");
                  var d = document.createElement("thead");
                  var e = document.createElement("tr");
                  var f = "";
                  settings.columns[index].autocomplete.multipleColumn.forEach(
                    (autocol, autokey) => {
                      var width = "";
                      width =
                        settings.columns[index].autocomplete
                          .multipleColumnWidth[autokey];
                      f += "<th width='" + width + "'>" + autocol + "</th>";
                    }
                  );
                  e.innerHTML = f;
                  d.appendChild(e);
                  c.appendChild(d);
                  b.appendChild(c);
                  a.appendChild(b);
                  // row table
                  var g = document.createElement("tbody");
                  var i = [];

                  if (data.length == 0) {
                    var tr = document.createElement("tr");
                    tr.classList.add("gridcomplete-table-row");
                    tr.innerHTML = '<td colspan="99">Data tidak ditemukan</td>';

                    if (
                      settings.columns[index].autocompleteOnEmptyAction.title !=
                      ""
                    ) {
                      tr.innerHTML =
                        '<td class="text-center" colspan="99">' +
                        settings.columns[index].autocompleteOnEmptyAction
                          .title +
                        "</td>";
                    }

                    if (
                      $.isFunction(
                        settings.columns[index].autocompleteOnEmptyAction
                      )
                    ) {
                      settings.columns[
                        index
                      ].autocomplete.autocompleteOnEmptyAction.call(obj);
                    }

                    tr.addEventListener("click", function (e) {
                      if (
                        $.isFunction(
                          settings.columns[index].autocompleteOnEmptyAction
                            .action
                        )
                      ) {
                        settings.columns[
                          index
                        ].autocompleteOnEmptyAction.action.call(obj);
                      }
                      closeAllLists();
                    });

                    g.appendChild(tr);
                  } else {
                    data.forEach((item, key) => {
                      var tr = document.createElement("tr");
                      tr.classList.add("gridcomplete-table-row");
                      var j = "";
                      item.forEach((colVal, colKey) => {
                        if (
                          colKey ==
                          settings.columns[index].autocomplete.selectedIndex
                        ) {
                          if (
                            colKey <
                            settings.columns[index].autocomplete.multipleColumn
                              .length
                          ) {
                            j +=
                              "<td>" +
                              "<strong>" +
                              colVal.substr(0, val.length) +
                              "</strong>" +
                              colVal.substr(val.length) +
                              "</td>";
                          }
                        } else {
                          if (
                            colKey <
                            settings.columns[index].autocomplete.multipleColumn
                              .length
                          ) {
                            j += "<td>" + colVal + "</td>";
                          }
                        }
                      });
                      tr.innerHTML = j;
                      item.forEach((colVal, colKey) => {
                        tr.innerHTML +=
                          "<input type='hidden' value='" + colVal + "'>";
                      });
                      var lastClick = 0;
                      var delay = 20;
                      tr.addEventListener("click", function (e) {
                        if (lastClick >= Date.now() - delay) return;
                        lastClick = Date.now();
                        // /*insert the value for the autocomplete text field:*/
                        $(obj).val(
                          this.getElementsByTagName("input")[
                            settings.columns[index].autocomplete.selectedIndex
                          ].value
                        );
                        if (
                          typeof settings.columns[index].autocomplete
                            .eachByMultipleColumn === "undefined"
                        ) {
                          settings.columns[
                            index
                          ].autocomplete.multipleColumn.forEach((v, k) => {
                            $(obj).data(
                              "val" + k,
                              this.getElementsByTagName("input")[k].value
                            );
                          });
                        } else {
                          item.forEach(function (itemsss, key) {
                            $(obj).data("val" + key, itemsss);
                          });
                        }
                        if ($.isFunction(settings.columns[index].onchange)) {
                          settings.columns[index].onchange.call(obj);
                        }
                        // /*close the list of autocompleted values,
                        //         (or any other open lists of autocompleted values:*/
                        closeAllLists();
                      });
                      i.push(tr);
                      //
                    });
                    i.forEach((el) => {
                      g.appendChild(el);
                    });
                  }

                  c.appendChild(g);
                  b.appendChild(c);
                  a.appendChild(b);
                  // if (data.length == []) {
                  //   b = document.createElement("DIV");
                  //   b.setAttribute("class", "gridcomplete-empty-container");
                  //   var c = document.createElement("button");
                  //   c.textContent = "";
                  //   c.setAttribute("class", "btn btn-primary");
                  //   b.appendChild(c);
                  //   a.appendChild(b);
                  // } else {

                  // }
                },
              });
            }
          }

          /*execute a function presses a key on the keyboard:*/
          obj.on("keydown", function (e) {
            var x = document.getElementById(this.id + "_gridcomplete-list");
            if (settings.columns[index].autocomplete.type == "single") {
              if (x) x = x.getElementsByClassName("gridcomplete-item");
            }
            if (settings.columns[index].autocomplete.type == "multiple") {
              if (x) x = x.getElementsByTagName("tbody");
              if (x) {
                if (x.length > 0) {
                  x = x[0].getElementsByTagName("tr");
                }
              }
            }
            if (e.keyCode == 40) {
              /*If the arrow DOWN key is pressed,
              increase the currentFocus variable:*/
              // if (currentFocus >= x.length) currentFocus = 0;
              // else currentFocus++;
              currentFocus += 1;
              /*and and make the current item more visible:*/
              addActive(x);
            } else if (e.keyCode == 38) {
              //up
              /*If the arrow UP key is pressed,
              decrease the currentFocus variable:*/
              // if (currentFocus < 0) currentFocus = x.length - 1;
              // else currentFocus--;
              currentFocus -= 1;
              /*and and make the current item more visible:*/
              addActive(x);
            } else if (e.keyCode == 13) {
              closeAllLists(e.target);
              /*If the ENTER key is pressed, prevent the form from being submitted,*/
              e.preventDefault();
              if (currentFocus > -1) {
                /*and simulate a click on the "active" item:*/
                setTimeout(() => {
                  if (x) x[currentFocus].click();
                  currentFocus = -1;
                }, 100);
              }
              // $(this).unbind("keydown");
            } else if (e.keyCode == 9) {
              closeAllLists(e.target);
              /*tab,*/
            } else {
              currentFocus = -1;
            }
          });

          function addActive(x) {
            /*a function to classify an item as "active":*/
            if (!x) return false;
            /*start by removing the "active" class on all items:*/
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = x.length - 1;
            /*add class "gridcomplete-active":*/
            x[currentFocus].classList.add("gridcomplete-active");
            // add by noyr
            x[currentFocus].scrollIntoView({
              behavior: "smooth",
              block: "center",
            });
            // end by noyr
          }

          function removeActive(x) {
            /*a function to remove the "active" class from all autocomplete items:*/
            for (var i = 0; i < x.length; i++) {
              x[i].classList.remove("gridcomplete-active");
            }
          }

          function closeAllLists(elmnt) {
            /*close all autocomplete lists in the document,
          except the one passed as an argument:*/
            var x = document.getElementsByClassName("gridcomplete-items");
            for (var i = 0; i < x.length; i++) {
              if (elmnt != x[i] && elmnt != obj) {
                x[i].parentNode.removeChild(x[i]);
              }
            }
          }

          /*execute a function when someone clicks in the document:*/
          document.addEventListener("click", function (e) {
            var nodeValue = "";
            if (typeof e.target.attributes["data-col"]?.value !== "undefined") {
              nodeValue = e.target.attributes["data-col"]?.value;
            }
            if (nodeValue != index.toString()) {
              closeAllLists(e.target);
            }
          });
        }, 300)
      );
    }

    function selectcomplete(obj, index, value) {
      var tid = setInterval(function () {
        if (document.readyState !== "complete") return;
        clearInterval(tid);
        if (settings.columns[index].select.remoteType == "static") {
          var selectEmpty = true;
          var valEmpty = "- Pilih -";
          if (typeof settings.columns[index].select.valEmpty === "undefined") {
            selectEmpty = false;
            valEmpty = "";
          } else if (settings.columns[index].select.valEmpty == "") {
            selectEmpty = false;
            valEmpty = "";
          } else {
            selectEmpty = true;
            valEmpty = settings.columns[index].select.valEmpty;
          }
          if (selectEmpty == true) {
            $("#" + $(obj)[0]["id"]).append(
              $("<option>", {
                value: "",
                text: valEmpty,
              })
            );
          }
          for (
            i = 0;
            i < settings.columns[index].select.dataStatic.length;
            i++
          ) {
            $("#" + $(obj)[0]["id"]).append(
              $("<option>", {
                value: settings.columns[index].select.dataStatic[i]["val"],
                text: settings.columns[index].select.dataStatic[i]["data"],
              })
            );

            if (value != "") {
              $("#" + $(obj)[0]["id"])
                .find("option")
                .each(function (i, e) {
                  if ($(e).val() == value) {
                    $("#" + $(obj)[0]["id"]).prop("selectedIndex", i);
                  }
                });
            }
          }
        } else if (settings.columns[index].select.remoteType == "ajax") {
          // var postData = [];
          // // jqxhr.abort();
          // jqxhr = $.ajax({
          //   type: "post",
          //   url: settings.columns[index].select.ajaxUrl,
          //   dataType: "json",
          //   data: postData,
          //   success: function (data) {
          //     for (i = 0; i < data.length; i++) {
          //       $('#' + $(obj)[0]['id']).append($('<option>', {
          //         value: data[i][0],
          //         text : data[i][1]
          //       }));
          //     }
          //   },
          // });
        }
      }, 100);
    }

    function selectMultiplecomplete(obj, index, value) {
      var tid = setInterval(function () {
        if (document.readyState !== "complete") return;
        clearInterval(tid);
        if (settings.columns[index].select.remoteType == "static") {
          var selectEmpty = true;
          var valEmpty = "- Pilih -";
          if (typeof settings.columns[index].select.valEmpty === "undefined") {
            selectEmpty = false;
            valEmpty = "";
          } else if (settings.columns[index].select.valEmpty == "") {
            selectEmpty = false;
            valEmpty = "";
          } else {
            selectEmpty = true;
            valEmpty = settings.columns[index].select.valEmpty;
          }
          if (selectEmpty == true) {
            $("#" + $(obj)[0]["id"]).append(
              $("<option>", {
                value: "",
                text: valEmpty,
              })
            );
          }
          for (
            i = 0;
            i < settings.columns[index].select.dataStatic.length;
            i++
          ) {
            console.log(value);
            $("#" + $(obj)[0]["id"]).append(
              $("<option>", {
                value: settings.columns[index].select.dataStatic[i]["val"],
                text: settings.columns[index].select.dataStatic[i]["data"],
              })
            );

            if (value != "") {
              $("#" + $(obj)[0]["id"])
                .val(value)
                .change();
            }
          }
        }
      }, 100);
    }

    function checkboxmultiple(obj, index, value) {
      var tid = setInterval(function () {
        if (document.readyState !== "complete") return;
        clearInterval(tid);
        var dataRow = $("#" + $(obj)[0]["id"]).attr("data-row");
        var dataCol = $("#" + $(obj)[0]["id"]).attr("data-col");
        var dataTableId = $("#" + $(obj)[0]["id"]).attr("data-tableid");
        var dataName = $("#" + $(obj)[0]["id"]).attr("data-name");
        for (
          i = 0;
          i < settings.columns[index].checkbox.dataStatic.length;
          i++
        ) {
          let checked = "";

          if (
            jQuery.inArray(
              settings.columns[index].checkbox.dataStatic[i]["val"],
              value
            ) !== -1
          ) {
            checked = "checked";
          }

          $("#" + $(obj)[0]["id"]).append(
            '<label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="' +
              dataName +
              "_" +
              dataRow +
              '[]" id="' +
              settings.columns[index].checkbox.dataStatic[i]["val"] +
              "_" +
              dataTableId +
              "_gridtable_cell_" +
              dataRow +
              "_" +
              dataCol +
              '" data-row="' +
              dataRow +
              '" data-col="' +
              dataCol +
              '" value="' +
              settings.columns[index].checkbox.dataStatic[i]["val"] +
              '" ' +
              checked +
              ' ><span class="form-check-label">' +
              settings.columns[index].checkbox.dataStatic[i]["data"] +
              "</span></label>"
          );
        }
      }, 100);
    }

    return gridTable;
  };
})(jQuery);
