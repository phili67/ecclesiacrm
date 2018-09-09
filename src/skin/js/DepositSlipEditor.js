function initPaymentTable(type)
{
  var colDef = [
    {
      width: 'auto',
      title:i18next.t('Family')+' '+i18next.t('or')+' '+i18next.t('Person'),
      data:'FamilyString',
      render: function(data, type, full, meta) {
        var familyName = data ? data : i18next.t('Anonymous');
        /*var res = ((is_closed == 0)?'<a href=\'PledgeEditor.php?linkBack=DepositSlipEditor.php?DepositSlipID=' + depositSlipID +
            '&GroupKey=' + full.Groupkey + '\'><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa '+  (isDepositClosed ? "fa-search-plus": "fa-pencil" ) +' fa-stack-1x fa-inverse"></i></span></a>':'');
        res+=familyName;*/
        
        var res = '<a href=\'PledgeEditor.php?linkBack=DepositSlipEditor.php?DepositSlipID=' + depositSlipID +
            '&GroupKey=' + full.Groupkey + '\'><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa '+  (isDepositClosed ? "fa-search-plus": "fa-pencil" ) +' fa-stack-1x fa-inverse"></i></span></a>';
            
        res+=familyName;
        
        return res;
      }
    },
    {
      width: 'auto',
      title:i18next.t('Fund'),
      data:'DonationFundNames',
      render: function (data, type, full, meta) {
          return data;
      }
    }, 
    {
      width: 'auto',
      title:i18next.t('Pledge or payment'),
      data:'Pledgeorpayment',
      render: function (data, type, full, meta) {
        if (data == null) {
          return i18next.t("None");
        } else {
          return i18next.t(data);
        }
      }
    },   
    {
      width: 'auto',
      title:i18next.t('Amount'),
      data:'sumAmount',
      render: function (data, type, full, meta) {
        return Number(data).toLocaleString(window.CRM.lang);
      }
    },
    {
      width: 'auto',
      title:i18next.t('Method'),
      data:'Method',
      render: function (data, type, full, meta) {
          return i18next.t(data);
      }
    }
  ];

  if (!(type == 'BankDraft' || type == 'CreditCard')) {
    colDef.push(
      {
        width: 'auto',
        title:i18next.t('Check Number'),
        data:'Checkno',
        render: function(data, type, full, meta)
        {
          if (data == null || data == "0") {
          return i18next.t("None");
        } else {
          return i18next.t(data);
        }
        }
      }
    );  
  }
  
  if ( depositType == "CreditCard" )
  {
    colDef.push(
      {
        width: 'auto',
        title:i18next.t('Details'),
        data:'Id',
        render: function(data, type, full, meta)
        {
          return '<button type="button" data-GK="' + full.Groupkey + '" class="btn btn-info detailButton">'+i18next.t("Details")+'</button>'
        }
      }
    );
  }

  dataT = $("#paymentsTable").DataTable({
    ajax:{
      url :window.CRM.root+"/api/deposits/"+depositSlipID+"/pledges",
      dataSrc:''
    },
      "language": {
          "url": window.CRM.plugin.dataTable.language.url
      },
    columns: colDef,
    responsive: true,
    "createdRow" : function (row,data,index) {
      $(row).addClass("paymentRow");
    }
  });
  dataT.on( 'xhr', function () {
   var json = dataT.ajax.json();
   console.log( json );
  });
}

function initDepositSlipEditor()
{
  function format(d) {
    // `d` is the original data object for the row
    return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' +
            '<tr>' +
            '<td>Date:</td>' +
            '<td>' + moment(d.Date).format(window.CRM.datePickerformat.toUpperCase()) + '</td>' +
            '</tr>' +
            '<tr>' +
            '<td>Fiscal Year:</td>' +
            '<td>' + d.Fyid + '</td>' +
            '</tr>' +
            '<tr>' +
            '<td>Fund(s):</td>' +
            '<td>' + d.DonationFundName + '</td>' +
            '</tr>' +
            '<tr>' +
            '<td>Non Deductible:</td>' +
            '<td>' + d.Nondeductible + '</td>' +
            '</tr>' +
            '<tr>' +
            '<td>Comment:</td>' +
            '<td>' + d.Comment + '</td>' +
            '</tr>' +
            '</table>';
  }
  
  $("#DepositSlipSubmit").click(function(e) {
    e.preventDefault();
    var formData = {
      'depositDate': moment($('#DepositDate').val(),window.CRM.datePickerformat.toUpperCase()).format('YYYY-MM-DD'),
      'depositComment': $("#Comment").val(),
      'depositClosed': $('#Closed').is(':checked'),
      'depositType': depositType

    };

    //process the form
    $.ajax({
      type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
      url: window.CRM.root+'/api/deposits/' + depositSlipID, // the url where we want to POST
      data: JSON.stringify(formData), // our data object
      dataType: 'json', // what type of data do we expect back from the server
      contentType: "application/json; charset=utf-8",
      encode: true
    })
      .done(function(data) {
          location.reload();
      }).fail(function() {
    });
  });

  $('#paymentsTable tbody').on('click', 'td.details-control', function() {
    var tr = $(this).closest('tr');
    var row = dataT.row(tr);
    if(row.child.isShown()) {
      // This row is already open - close it
      row.child.hide();
      tr.removeClass('shown');
      $(this).html('<i class="fa fa-plus-circle"></i>');
    }
    else {
      // Open this row
      row.child(format(row.data())).show();
      tr.addClass('shown');
      $(this).html('<i class="fa fa-minus-circle"></i>');
    }
  });

  $(document).on('click',".paymentRow", function(event) {
    if (! ($(event.target).hasClass("details-control") || $(event.target).hasClass("fa")))
    {
      $(this).toggleClass('selected');
      var selectedRows = dataT.rows('.selected').data().length;
      $("#deleteSelectedRows").prop('disabled', !(selectedRows));
      $("#deleteSelectedRows").text(i18next.t("Delete")+" (" + selectedRows + ") "+i18next.t("Selected Rows"));
    }


  });

}

function initCharts(fundChartData, pledgeChartData)
{
  var pieOptions = {
    //String - Point label font colour
    pointLabelFontColor: "#666",
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 2,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    //String - A legend template
    legendTemplate: "<% for (var i=0; i<segments.length; i++){%><span style=\"color: white;padding-right: 4px;padding-left: 2px;background-color:<%=segments[i].fillColor%>\"><%if(segments[i].label){%><%=segments[i].label%><%}%></span> <%}%></ul>"
  };

  pieChartCanvas = $("#type-donut").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
  pieChart = pieChart.Doughnut(fundChartData, pieOptions);
  var legend = pieChart.generateLegend();
  $('#type-donut-legend').append(legend);

  var pieChartCanvas = $("#fund-donut").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
  pieChart = pieChart.Doughnut(pledgeChartData, pieOptions);
  var legend = pieChart.generateLegend();
  $('#fund-donut-legend').append(legend);

}
