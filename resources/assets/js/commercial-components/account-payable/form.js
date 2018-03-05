
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

Vue.component('account-payable-form',{
    props: ['taxpayer', 'cycle'],
    data() {
        return {
            id:0,
            taxpayer_id:'',
            chart_id:'',
            date:'',
            transaction_id:'',
            currency_id:'',
            rate:'',
            debit:'',
            credit:'',
            currencies:[],
            charts:[]
        }
    },


    methods: {


        //Takes Json and uploads it into Sales INvoice API for inserting. Since this is a new, it should directly insert without checking.
        //For updates code will be different and should use the ID's palced int he Json.
        onSave: function(json)
        {

            var app = this;
            var api = null;
            this.taxpayer_id=this.taxpayer;
            $.ajax({
                url: '',
                headers: {'X-CSRF-TOKEN': CSRF_TOKEN},
                type: 'post',
                data:json,
                dataType: 'json',
                async: false,
                success: function(data)
                {
                    if (data=='ok')
                    {
                        app.onReset();

                    }
                    else
                    {
                        alert('Something Went Wrong...')
                    }


                },
                error: function(xhr, status, error)
                {
                    console.log(xhr.responseText);
                }
            });
        },

        onReset: function()
        {
            var app=this;



            app.id=0;
            app.taxpayer_id= null;
            app.chart_id= null;
            app.date= null;
            app.transaction_id= null;
            app.currency_id= null;
            app.rate= null;
            app.debit= null;
            app.credit= null;
            app.$parent.status=0;

        },

        cancel()
        {
            var app=this;
            app.$parent.status=0;
        },
        getCurrencies: function(data)
        {
            var app=this;
            $.ajax({
                url: '/api/' + this.taxpayer + '/get_currency' ,
                  headers: {'X-CSRF-TOKEN': CSRF_TOKEN},
                type: 'get',
                dataType: 'json',
                async: true,
                success: function(data)
                {
                    app.currencies=[];
                    for(let i = 0; i < data.length; i++)
                    {
                        app.currencies.push({name:data[i]['name'],id:data[i]['id']});
                    }

                },
                error: function(xhr, status, error)
                {
                    console.log(xhr.responseText);
                }
            });
        },

        getRate: function()
          {

              var app=this;
              $.ajax({
                  url: '/api/' + this.taxpayer + '/get_rateByCurrency/' + app.currency_id + '/' + app.date  ,
                    headers: {'X-CSRF-TOKEN': CSRF_TOKEN},
                  type: 'get',
                  dataType: 'json',
                  async: true,
                  success: function(data)
                  {

                      if (app.rate=='' || app.rate==null) {
                          app.rate=data;
                      }


                  },
                  error: function(xhr, status, error)
                  {
                      console.log(xhr.responseText);
                  }
              });
          },
        onEdit: function(data)
        {
            var app = this;
            app.id = data.id;
            app.taxpayer_id = data.taxpayer_id;
            app.chart_id = data.chart_id;
            app.date = data.date;
            app.transaction_id = data.transaction_id;
            app.currency_id = data.currency_id;
            app.rate = data.rate;
            app.debit = data.debit;
            app.credit = data.credit;

        },

        getCharts: function(data)
        {
            var app=this;
            $.ajax({
                url: '/api/' + this.taxpayer + '/' + this.cycle + '/accounting/chart/get_account',
                  headers: {'X-CSRF-TOKEN': CSRF_TOKEN},
                type: 'get',
                dataType: 'json',
                async: true,
                success: function(data)
                {
                    app.charts = [];
                    for(let i = 0; i < data.length; i++)
                    {
                        app.charts.push({name:data[i]['name'],id:data[i]['id']});
                    }

                },
                error: function(xhr, status, error)
                {
                    console.log(xhr.responseText);
                }
            });
        }
    },

    mounted: function mounted()
    {
        this.getCurrencies();
        this.getCharts();
    }
});
