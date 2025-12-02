<div class="row text-center"> 
    @if ($type == 'PRESUPUESTO')
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
            <a style="margin: -6px !important; padding: 5px;" data-toggle="tooltip"  data-original-title="Edit" class="edit btn btn-primary disabled" > 
                <i class="fa-regular fa-money-bill-1"></i>
            </a>
        </div> 
    @else
        @if ($payment <= 0)
            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="mostrarBillPayment({{ $id }})" data-original-title="Edit" class="edit btn btn-primary"> 
                    <i class="fa-regular fa-money-bill-1"></i>
                </a>
            </div> 
        @else
            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="mostrarBillPayment({{ $id }})" data-original-title="Edit" class="edit btn btn-danger"> 
                    <i class="fa-regular fa-money-bill-1"></i>
                </a>
            </div> 
        @endif
    @endif
    <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
        <a style="margin: -6px !important; padding: 5px; color:white;"  href="{{ route('pdf', ['id' => $id]) }}" data-toggle="tooltip" target="_blank" data-original-title="Edit" class="edit btn btn-info"> 
            <i class="fa-regular fa-eye"></i>
        </a>
    </div>
    @if ($type == 'PRESUPUESTO')
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
            <a style="margin: -6px !important; padding: 5px; color:white;"  href="javascript:void(0)" data-toggle="tooltip" onClick="modalRepayment({{ $id }})" data-original-title="Edit" class="edit btn btn-warning disabled" > 
                <i class="fa-solid fa-reply"></i>
            </a>
        </div>
    @else
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
            <a style="margin: -6px !important; padding: 5px; color:white;"  href="javascript:void(0)" data-toggle="tooltip" onClick="modalRepayment({{ $id }})" data-original-title="Edit" class="edit btn btn-warning"> 
                <i class="fa-solid fa-reply"></i>
            </a>
        </div>
    @endif
</div>    