<div class="row text-center">
    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" style="padding:0;">
        <form class="form" action="{{ route('billWaitStore') }}" method="POST" style="margin-bottom: -5px;">
            @csrf
            {{-- Corrected 'hidden' type and descriptive name --}}
            <input name="id_billWait" type="hidden" value="{{ $id }}">
            <button class="btn btn-primary" style="margin: 0px; padding: 6px !important;" type="submit"><i class="fa-solid fa-share"></i></button>
        </form>
    </div>
</div>