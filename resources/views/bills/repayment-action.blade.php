{{-- filepath: resources/views/bills/repayment-action.blade.php --}}
<div class="row text-center"> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">  
        <a style="margin: -6px !important; padding: 5px; color:white;" href="{{ route('pdfNoteCredit', ['id' => $code]) }}" target="_blank" data-original-title="Nota de Crédito" class="edit btn btn-info"> 
            <i class="fa-regular fa-eye"></i>
        </a>
    </div> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">  
        <a style="margin: -6px !important; padding: 5px; color:white;" href="{{ route('pdfRepaymentDetail', ['code' => $code]) }}" target="_blank" data-original-title="Detalle Devolución" class="edit btn btn-warning"> 
            <i class="fa-solid fa-file-invoice"></i>
        </a>
    </div> 
</div>