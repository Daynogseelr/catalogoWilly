
<div class="row text-center"> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6" style="padding:0;">  
        <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id }})" data-original-title="Edit" class="edit btn btn-primary2 edit"> 
            <i class="fa-regular fa-pen-to-square"></i>
        </a>
    </div> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6" style="padding:0; margin-left: -10px;"> 
        <a class="cambia{{$id}}" href="javascript:void(0)"  onClick="micheckbox({{ $id }})" >
            @if ($status == '1')
                <i class="fa-solid fa-toggle-on text-success2 fs-4"  style="margin: -6px !important;" ></i>
            @else
                <i class="fa-solid fa-toggle-off text-danger2 fs-4" style="margin: -6px !important;" ></i>
            @endif
        </a>
    </div>
</div>    