
<div class="row text-center"> 
    @if (auth()->user()->type == "ADMINISTRADOR" || auth()->user()->type == "EMPRESA" || auth()->user()->type == "ADMINISTRATIVO" || auth()->user()->type == "SUPERVISOR")
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" style="padding:0;">  
            <a style="margin: -3px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id }})" data-original-title="Edit" class="edit btn btn-primary"> 
                <i class="fa-regular fa-pen-to-square"></i>
            </a>
        </div> 
    @endif
</div>    
            