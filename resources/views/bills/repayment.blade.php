@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'repayment'])
@section('content')
<div class="container-fluid ">
    <div class="row ">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <div class="row">
                        <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                            <div class="row">
                                <div class="col-12 col-sm-12">
                                    <h4>{{__('Repayments')}}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="card-body" >
                        {!! $dataTable->table(['class' => 'table table-striped table-bordered', 'style' => 'font-size:13px;width:98%!important;'], true) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('footer')
@endsection
@section('scripts')
    {!! $dataTable->scripts() !!}
@endsection