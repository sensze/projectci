<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"><?= $title; ?></h1>

    <div class="row">
        <div class="col-lg-6">
            <?= $this->session->flashdata('message') ?>
            <form action="<?= base_url('user/laporanKP') ?>" method="post">
                <div class="form-group">
                    <label for="tanggal">Tanggal KP</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal">
                    <?= form_error('tanggal', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <label for="jam_mulai">Jam Mulai KP</label>
                    <input type="time" class="form-control" id="jam_mulai" name="jam_mulai">
                    <?= form_error('jam_mulai', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <label for="jam_selesai">Jam Berakhir KP</label>
                    <input type="time" class="form-control" id="jam_selesai" name="jam_selesai">
                    <?= form_error('jam_selesai', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <label for="materi">Materi Kegiatan</label>
                    <input type="text" class="form-control" id="materi" name="materi">
                    <?= form_error('materi', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <label for="progres">Unggah bukti KP</label>
                    <input type="file" class="form-control" id="progres" name="progres">
                    <?= form_error('progres', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit Laporan</button>
                </div>
            </form>
        </div>
    </div>


</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->