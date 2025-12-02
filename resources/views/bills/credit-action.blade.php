<div class="row text-center"> 
    <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3" style="padding:5px;">  
        <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="credit({{ $id }})" data-original-title="Edit" class="edit btn btn-danger"> 
            <i class="fa-regular fa-money-bill-1"></i>
        </a>
    </div> 
    <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3" style="padding:5px;">  
        <a style="margin: -6px !important; padding: 5px; color:white;"  href="{{ route('pdfCreditPayment', ['id' => $id]) }}" data-toggle="tooltip" target="_blank" data-original-title="Edit" class="edit btn btn-info"> 
            <i class="fa-solid fa-file-invoice"></i>
        </a>
    </div>
    <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3" style="padding:5px;">  
        <a style="margin: -6px !important; padding: 5px; color:white;"  href="{{ route('pdf', ['id' => $id]) }}" data-toggle="tooltip" target="_blank" data-original-title="Edit" class="edit btn btn-info"> 
            <i class="fa-regular fa-eye"></i>
        </a>
    </div>
    <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3" style="padding:5px;">  
        @if(!empty($phone))
            <a style="margin: -6px !important; padding: 5px; background-color: #25D366; !important;" class="whatsapp-button btn edit" href="{{ route('openWhatsAppChatCredit', ['phone' => $phone, 'id_client' => $id_client]) }}" target="_blank">
                <i class="fa-brands fa-whatsapp"  style="color: White  !important;"></i>
            </a>
        @endif
    </div> 