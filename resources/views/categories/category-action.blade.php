
<div class="row text-center"> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6" style="padding:0;">  
        <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{  $id }})" data-original-title="Edit" class="btn btn-primary"> 
            <i class="fa-regular fa-pen-to-square"></i>
        </a>
    </div> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6" style="padding:0;"> 
        <a style="margin: -6px !important; padding: 5px;" class="cambia{{ $id}}" href="javascript:void(0)"  onClick="micheckbox({{  $id }})" >
            @if ($status == '1')
                <i style="margin: -5px !important; padding: 0px !important;" class="fa-solid fa-toggle-on text-success fs-4"></i>
            @else
                <i style="margin: -5px !important; padding: 0px !important;"  class="fa-solid fa-toggle-off text-danger fs-4"></i>
            @endif
        </a>
    </div>
</div>    
            