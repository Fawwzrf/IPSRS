<form action="{{ $form_act }}" method="post" id="form-ajax">
    @csrf
    <input type="hidden" name="penugasan_id" value="{{ $penugasan_id }}">
    
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label required">Alasan Penolakan</label>
            <textarea class="form-control" name="alasan" rows="3" required placeholder="Jelaskan alasan penolakan tugas ini..."></textarea>
            <div class="form-text text-muted">Alasan akan ditampilkan ke admin/supervisor.</div>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-times"></i> Konfirmasi Tolak
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-arrow-left"></i> Batal
        </button>
    </div>
</form>
