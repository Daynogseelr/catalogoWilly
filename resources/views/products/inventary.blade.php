@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'inventary'])
@section('content')
<div class="container-fluid py-1">
    <div class="row mt-1">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <div class="row">
                        <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                            <div class="row">
                                <div class="col-10 col-sm-11">
                                    <h4>{{__('Visualizar Ajustes de Inventario')}}</h4>
                                </div>
                                <div class="col-2 col-sm-1 text-end">
                                    <a class="btn btn-danger2" href="{{ route('indexStocktaking')}}">
                                        <i class="fa-solid fa-circle-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="card-body">
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