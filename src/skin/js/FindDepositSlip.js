var dataT = 0;

$(document).ready(function () {
  $("#depositDate").datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang}).datepicker("setDate", new Date());
  
  $("#addNewDeposit").click(function (e) {
    var newDeposit = {
      'depositType': $("#depositType option:selected").val(),
      'depositComment': $("#depositComment").val(),
      'depositDate': moment($("#depositDate").val(),window.CRM.datePickerformat.toUpperCase()).format('YYYY-MM-DD'),
      //'depositFund': $("#depositFund option:selected").val()
    };
    $.ajax({
      method: "POST",
      url: window.CRM.root + "/api/deposits",
      data: JSON.stringify(newDeposit),
      contentType: "application/json; charset=utf-8",
      dataType: "json"
    }).done(function (data) {
      data.totalAmount = '';
      dataT.row.add(data);
      dataT.rows().invalidate().draw(true);
      
      $(".count-deposit").html(dataT.column( 0 ).data().length);
      
      $(".deposit-current-deposit-item").show();
      
      dataT.ajax.reload();
    });
  });

  dataT = $("#depositsTable").DataTable({
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    responsive: true,
    ajax: {
      url: window.CRM.root + "/api/deposits",
      dataSrc: "Deposits"
    },
    "deferRender": true,
    columns: [
      {
        title:i18next.t('Deposit ID'),
        data: 'Id',
        render: function (data, type, full, meta) {
          if (type === 'display') {
            return '<a href=\'DepositSlipEditor.php?DepositSlipID=' + full.Id + '\'><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-search-plus fa-stack-1x fa-inverse"></i></span></a>' + full.Id;
          }
          else {
            return parseInt(full.Id);
          }
        },
        type: 'num'
      },
      /*{
        title:i18next.t('Fund'),
        data: 'fundName',
        render: function (data, type, full, meta) {
          if (data) {
            return data;
          } else {
            return i18next.t('None');
          }
        }
      },*/      
      {
        title:i18next.t('Deposit Date'),
        data: 'Date',
        render: function (data, type, full, meta) {
          if (type === 'display') {
            return moment(data).format(window.CRM.datePickerformat.toUpperCase());
          }
          else {
            return data
          }
        },
        searchable: true
      },
      {
        title:i18next.t('Deposit Total'),
        data: 'totalAmount',
        searchable: false,
        render: function (data, type, full, meta) {
          return Number(data).toLocaleString(window.CRM.lang,{maximumSignificantDigits : 21});;
        }
      },
      {
        title:i18next.t('Deposit Comment'),
        data: 'Comment',
        searchable: true
      },
      {
        title:i18next.t('Closed'),
        data: 'Closed',
        searchable: true,
        render: function (data, type, full, meta) {
          return data == 1 ? '<div style="color:red;text-align:center">'+i18next.t('Yes')+'</div>' : '<div style="color:green;text-align:center">'+i18next.t('No')+'</div>';
        }
      },
      {
        title:i18next.t('Deposit Type'),
        data: 'Type',
        searchable: true,
        render: function (data, type, full, meta) {
          return i18next.t(data);
        }
      }
    ],
    order: [0, 'desc']
  });

  $("#depositsTable tbody").on('click', 'tr', function () {
    $(this).toggleClass('selected');
    var selectedRows = dataT.rows('.selected').data().length;
    $("#deleteSelectedRows").prop('disabled', !(selectedRows));
    $("#deleteSelectedRows").text(i18next.t("Delete")+" ("+ selectedRows + ") "+i18next.t("Selected Rows"));
    $("#exportSelectedRows").prop('disabled', !(selectedRows));
    $("#exportSelectedRows").html("<i class=\"fa fa-download\"></i> "+i18next.t("Export")+" (" + selectedRows + ") "+i18next.t("Selected Rows")+" (OFX)");
    $("#exportSelectedRowsCSV").prop('disabled', !(selectedRows));
    $("#exportSelectedRowsCSV").html("<i class=\"fa fa-download\"></i> "+i18next.t("Export")+" (" + selectedRows + ") "+i18next.t("Selected Rows")+" (CSV)");
    $("#generateDepositSlip").prop('disabled', !(selectedRows));
    $("#generateDepositSlip").html("<i class=\"fa fa-download\"></i> "+i18next.t("Generate Deposit Slip for Selected")+" (" + selectedRows + ") "+i18next.t("Rows")+" (PDF)");
  });

  $('.exportButton').click(function (sender) {
    var selectedRows = dataT.rows('.selected').data()
    var type = this.getAttribute("data-exportType");
    $.each(selectedRows, function (index, value) {
      window.CRM.VerifyThenLoadAPIContent(window.CRM.root + '/api/deposits/' + value.Id + '/' + type);
    });
  });

});
