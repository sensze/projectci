<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"><?= $title; ?></h1>

    <div class="row">
        <div class="col-lg-6">
        <?= $this->session->flashdata('message') ?>
            <form action="<?= base_url('user/daftarKP') ?>" method="post">
                <div class="form-group">
                    <label for="judul">Judul KP</label>
                    <input type="text" class="form-control" id="judul" name="judul">
                    <?= form_error('judul', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <label for="tempat">Nama Perusahaan</label>
                    <input type="text" class="form-control" id="tempat" name="tempat">
                    <?= form_error('tempat', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat KP</label>
                    <input type="text" class="form-control" id="alamat" name="alamat">
                    <?= form_error('alamat', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <label for="waktu">Waktu Mulai</label>
                    <input type="date" class="form-control" id="waktu" name="waktu">
                    <?= form_error('waktu', '<small class="text-danger">', '</small>') ?>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Daftar KP</button>
                </div>
            </form>
        </div>
    </div>


</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->