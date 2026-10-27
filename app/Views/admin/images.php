<?= $this->extend('admin/Layouts/default') ?>
<?= $this->section('content') ?>
<div class="row"><div class="col-12">
    <div class="page-title-box d-sm-flex align-items-center justify-content-between"><h4 class="mb-sm-0">Images Library</h4></div>
    <div class="card"><div class="card-body">
        <h5 class="card-title">Upload images</h5>
        <p class="text-muted">Upload diagrams and illustrations, then copy their public URLs into the question editor or share them with ChatGPT. Your library includes images from your question packages.</p>
        <form id="imageUploadForm" action="<?= site_url('admin/images/upload') ?>" enctype="multipart/form-data" method="post" data-upload-form>
            <div class="form-group"><label for="libraryImages">Choose images</label>
                <input class="form-control-file" id="libraryImages" type="file" name="images[]" accept=".png,.jpg,.jpeg,.webp,.gif" multiple required aria-describedby="imageUploadHelp">
                <small id="imageUploadHelp" class="form-text text-muted">PNG, JPG, JPEG, WebP or GIF. Up to 10 images, 5 MB each and 25 MB total. Images are publicly accessible to anyone with their URL.</small>
            </div>
            <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-upload"></i> Upload Images</button>
            <a class="btn btn-sm btn-outline-primary d-none" href="<?= site_url('admin/images') ?>" data-upload-refresh>View uploaded images</a>
            <div class="mt-3 d-none" data-upload-result role="status" aria-live="polite"></div>
        </form>
    </div></div>
    <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Your images</h5></div>
    <?php if (!$images): ?>
        <div class="card"><div class="card-body text-center text-muted py-5"><i class="fa fa-images fa-2x mb-3"></i><p class="mb-0">Your library is empty. Upload your first image above.</p></div></div>
    <?php else: ?>
        <div class="row">
        <?php foreach ($images as $image): $url = site_url('images/' . $image['public_id']); ?>
            <div class="col-sm-6 col-lg-4 col-xl-3 mb-4"><div class="card h-100 mb-0">
                <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener" class="d-flex align-items-center justify-content-center bg-light p-3" style="height:180px;">
                    <img src="<?= esc($url, 'attr') ?>" alt="<?= esc($image['original_name'], 'attr') ?>" loading="lazy" style="max-width:100%;max-height:100%;object-fit:contain;">
                </a>
                <div class="card-body p-3"><div class="text-truncate font-weight-bold" title="<?= esc($image['original_name'], 'attr') ?>"><?= esc($image['original_name']) ?></div>
                    <small class="text-muted d-block mb-2"><?= (int) $image['width'] ?> × <?= (int) $image['height'] ?> · <?= number_format($image['file_size'] / 1024) ?> KB</small>
                    <label class="sr-only" for="image-url-<?= (int) $image['id'] ?>">Public URL for <?= esc($image['original_name']) ?></label>
                    <input class="form-control form-control-sm mb-2" id="image-url-<?= (int) $image['id'] ?>" value="<?= esc($url, 'attr') ?>" readonly onclick="this.select()">
                    <button class="btn btn-sm btn-outline-primary btn-block" type="button" data-copy-target="image-url-<?= (int) $image['id'] ?>"><i class="fa fa-copy"></i> Copy Public URL</button>
                </div>
            </div></div>
        <?php endforeach; ?>
        </div>
        <?= $pager->links() ?>
    <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
<?= $this->section('foot') ?>
<script>window.packageToolsConfig = <?= json_encode(['csrfHeader' => csrf_header(), 'csrfHash' => csrf_hash(), 'csrfCookie' => config('Cookie')->prefix . config('Security')->cookieName], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= base_url('public/assets/js/admin-package-tools.js') ?>"></script>
<?= $this->endSection() ?>
