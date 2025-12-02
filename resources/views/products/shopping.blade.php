@extends('app', ['page' => __('Compras'), 'pageSlug' => 'shopping'])
@section('content')

<div class="container-fluid ">
    <div class="row ">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <div class="row">
                        <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                            <div class="row">
                                <div class="col-10 col-sm-11">
                                    <h4>{{__('Compras')}}</h4>
                                </div>
                                <div class="col-2 col-sm-1">
                                    <a class="btn btn-danger2" href="{{ route('indexAddShopping') }}">
                                        <i class="fa-solid fa-circle-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive" style="font-size: 13px;">
                        {{-- DataTable --}}
                        {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'style' => 'font-size:13px;'], true) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('footer')
@endsection
@section('scripts')
    {{ $dataTable->scripts() }}
@endsection