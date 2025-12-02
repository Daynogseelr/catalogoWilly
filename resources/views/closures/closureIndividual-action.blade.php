<div class="row text-center"> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6" style="padding:0;">  
        <a style="margin: -6px !important; padding: 5px; color:white;"  href="{{ route('pdfClosure', ['id' => $id]) }}" data-toggle="tooltip" target="_blank" data-original-title="Edit" class="edit btn btn-info"> 
            <i class="fa-regular fa-eye"></i>
        </a>
    </div>
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6" style="padding:0;">  
        <a style="margin: -6px !important; padding: 6px; color:white;"  href="{{ route('pdfClosureDetail', ['id' => $id]) }}" data-toggle="tooltip" target="_blank" data-original-title="Edit" class="edit btn btn-info"> 
            <i class="fa-solid fa-file-invoice"></i>
        </a>
    </div>
</div>