
<meta name="csrf-token" content="{{ csrf_token() }}">
<transaction-form :trantype ="1"  inline-template>
    <div>
        <div class="row">
            <div class="col-6">
                <div class="form-group m-form__group row">
                    <label for="example-text-input" class="col-4 col-form-label">
                        <b>@lang('global.InvoiceDate')</b>
                    </label>
                    <div class="col-8">
                        <input type="date" class="form-control" v-model="date" />
                    </div>
                </div>
                <div class="form-group m-form__group row">
                    <label for="example-text-input" class="col-4 col-form-label">
                        @lang('commercial.Supplier')
                    </label>

                    <div class="col-8">
                        <router-view name="SearchBox" :url="/get_last_purchase/"  :taxpayer="{{ request()->route('taxPayer')->id }}" :cycle="{{ request()->route('cycle')->id }}" :country="{{ request()->route('taxPayer')->country }}"></router-view>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="form-group m-form__group row">
                    <label for="example-text-input" class="col-4 col-form-label">
                        @lang('commercial.Document')
                    </label>
                    <div class="col-8">
                        <div class="input-group">
                            <select v-model="document_id" required class="custom-select" v-on:change="changeDocument()">
                                <option v-for="document in $parent.documents" :value="document.id">@{{ document.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group m-form__group row">

                    @php
                    $documentCode = Config::get('countries.' . request()->route('taxPayer')->country . '.document-code');
                    @endphp

                    <label for="example-text-input" class="col-4 col-form-label">
                        <b>{{ $documentCode }}  &amp; @lang('global.Deadline')</b>
                    </label>
                    <div class="col-8">
                        <div class="row">
                            <div class="col-5">
                                <input class="form-control m-input" type="number" placeholder="Timbrado"  v-model="code">
                            </div>
                            <div class="col-7">
                                <input type="date" class="form-control m-input" v-model="code_expiry"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group m-form__group row">
                    <label for="example-text-input" class="col-4 col-form-label">
                        @lang('commercial.InvoiceNumber')
                    </label>
                    <div class="col-8">
                      <masked-input mask="{{ Config::get('countries.' . request()->route('taxPayer')->country . '.document-mask') }}"   v-model="number" />
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="row">

            <div class="col-6">
                <div class="form-group m-form__group row">
                    <label for="example-text-input" class="col-4 col-form-label">
                        @lang('commercial.Condition')
                    </label>
                    <div class="col-8">
                        <div class="input-group">
                            <input id="payment_condition" type="text" class="form-control" v-model="payment_condition" placeholder=" 0 = Contado 1 0 mas = credito "/>

                        </div>
                    </div>
                </div>
                <div class="form-group m-form__group row" v-if="payment_condition == 0">
                    <label for="example-text-input" class="col-4 col-form-label ">
                        @lang('commercial.Account')
                    </label>
                    <div class="col-8">
                        <div>
                            <select v-model="chart_account_id" required class="custom-select" id="account_id">
                                <option v-for="account in accounts" :value="account.id">@{{ account.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-6">
                <div class="form-group m-form__group row">
                    <label for="example-text-input" class="col-4 col-form-label">
                        @lang('commercial.Currency')
                    </label>
                    <div class="col-8">
                        <div class="input-group">
                            <select required v-model="currency_id" class="custom-select" v-on:change="getRate()">
                                <option v-for="currency in currencies" :value="currency.id">@{{ currency.name }}</option>
                            </select>
                            <input type="text" class="form-control" v-model="rate" />
                        </div>
                    </div>
                </div>


            </div>
        </div>

        {{-- Invoice Detail --}}
        <div class="m-portlet m-portlet--metal m-portlet--head-solid m-portlet--bordered">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon">
                            <i class="flaticon-calendar"></i>
                        </span>
                        <h3 class="m-portlet__head-text m--font-primary">
                            @lang('commercial.Detail')
                        </h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <a href="#" v-on:click="addDetail" class="btn btn-outline-primary btn-sm m-btn m-btn--icon">
                        <span>
                            <i class="la la-plus"></i>
                            <span>
                                @lang('global.Create')
                            </span>
                        </span>
                    </a>
                </div>
            </div>
            <div class="m-portlet__body">
                <div class="row">
                    <div class="col-2">
                        <span class="m--font-boldest">
                            @lang('commercial.Account')
                        </span>
                    </div>
                    <div class="col-2">
                        <span class="m--font-boldest">@lang('commercial.SalesTax')</span>
                    </div>
                    <div class="col-2">
                        <span class="m--font-boldest">@lang('commercial.Value')</span>
                    </div>
                    <div class="col-2">
                        <span class="m--font-boldest">@lang('commercial.Exempt')</span>
                    </div>
                    <div class="col-2">
                        <span class="m--font-boldest">@lang('commercial.Taxed')</span>
                    </div>
                    <div class="col-1">
                        <span class="m--font-boldest">@lang('commercial.SalesTax')</span>
                    </div>
                    <div class="col-1">
                        <span class="m--font-boldest"></span>
                    </div>
                </div>

                <hr>

                <div class="row" v-for="detail in details">
                    <div class="col-2">
                        <select required  v-model="detail.chart_id" class="custom-select">
                            <option v-for="item in charts" :value="item.id">@{{ item.name }}</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <select required  v-model="detail.chart_vat_id" @change="onPriceChange(detail)" class="custom-select">
                            <option v-for="vat in vats" :value="vat.id">@{{ vat.name }}</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <input type="text" class="form-control" v-model="detail.value" @change="onPriceChange(detail)"/>
                    </div>
                    <div class="col-2">
                        @{{ new Number(detail.taxExempt).toLocaleString() }}
                    </div>
                    <div class="col-2">
                        @{{ new Number(detail.taxable).toLocaleString() }}
                    </div>
                    <div class="col-1">
                        @{{ new Number(detail.vat).toLocaleString() }}
                    </div>
                    <div class="col-1">
                        <input type="hidden" :value="grandTotal"/>
                        <button v-on:click="deleteDetail(detail)" class="btn btn-outline-danger m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill m-btn--air">
                            <i class="la la-remove"></i>
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-2">

                    </div>
                    <div class="col-2">
                        <span class="m--font-boldest">@lang('global.Total')</span>
                    </div>
                    <div class="col-2">

                        <span class="m--font-boldest">@{{ new Number(grandTotal).toLocaleString() }}</span>
                    </div>
                    <div class="col-2">
                        <span class="m--font-boldest">@{{ new Number(grandTaxExempt).toLocaleString() }}</span>
                    </div>
                    <div class="col-2">
                        <span class="m--font-boldest">@{{ new Number(grandTaxable).toLocaleString() }}</span>
                    </div>
                    <div class="col-1">
                        <span class="m--font-boldest">@{{ new Number(grandVAT).toLocaleString() }}</span>
                    </div>
                    <div class="col-1">

                    </div>
                </div>
            </div>
        </div>

        <button v-on:click="onSave($data,false)" class="btn btn-primary">
                @lang('global.Save')
            </button>
            <button v-on:click="onSave($data,true)" class="btn btn-primary">
                @lang('global.Save-and-New')
            </button>
        <button v-on:click="$parent.cancel()" class="btn btn-default">
            @lang('global.Cancel')
        </button>
    </div>
</transaction-form>
