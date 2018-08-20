<meta name="csrf-token" content="{{ csrf_token() }}">
<div>
  <div class="row">
    <div class="col-3">
      <span class="m--font-boldest">
        @lang('accounting.Accounts')
      </span>
    </div>
    <div class="col-3">
      <span class="m--font-boldest">
        @lang('accounting.Chart')
      </span>
    </div>
    <div class="col-2">
      <span class="m--font-boldest">
        @lang('accounting.Debit')
      </span>
    </div>
    <div class="col-2">
      <span class="m--font-boldest">
        @lang('accounting.Credit')
      </span>
    </div>

  </div>

  <div class="row m--margin-5" v-for="data in list">
    <div class="col-2 m--align-left">
      <span class="m-badge m-badge--info m-badge--wide m-badge--rounded">
        <b>@{{ data.chart.name }}</b>
      </span>

    </div>

    <div class="col-5">
      <span class="m--font-bolder m--font-metal m--font-transform-u">
        @{{ data.transaction.number }}
      </span>
    </div>
    <div class="col-5">
      <span class="m--font-bolder m--font-metal m--font-transform-u">
        @{{ data.transaction.debit }}
      </span>
    </div>
    <div class="col-5">
      <span class="m--font-bolder m--font-metal m--font-transform-u">
        @{{ data.transaction.credit }}
      </span>
    </div>

  </div>

  <infinite-loading force-use-infinite-wrapper="true" @infinite="infiniteHandler">
    <span slot="no-more">
      No more data
    </span>
  </infinite-loading>
</div>
