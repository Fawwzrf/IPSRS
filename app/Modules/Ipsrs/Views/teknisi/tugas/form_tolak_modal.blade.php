<form action="{{ $form_act }}" method="post" id="form-ajax">
    @csrf
    <input type="hidden" name="penugasan_id" value="{{ $penugasan_id }}">
    <div class="mb-3">
        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
        <textarea class="form-control" name="alasan" required autofocus></textarea>
    </div>
    <button type="submit" class="btn btn-danger">
        <i class="fas fa-paper-plane me-2"></i> Kirim
    </button>
</form>