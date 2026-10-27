<?php

namespace App\Controllers;

use App\Libraries\{ImageLibrary, PackageValidationException};
use App\Models\ImageLibraryModel;

class AdminImages extends BaseController
{
    public function index()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }
        if (!in_array($this->user['user_type'], ['admin', 'teacher'], true)) {
            return redirect()->to(base_url('/'));
        }
        $model = new ImageLibraryModel();
        return view('admin/images', [
            'pageTitle' => 'Images Library', 'user' => $this->user,
            'flashData' => $this->session->getFlashdata(),
            'images' => $model->where('owner_id', $this->user['id'])->orderBy('id', 'DESC')->paginate(24),
            'pager' => $model->pager,
        ]);
    }

    public function upload()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Your session has expired. Sign in again.']);
        }
        if (!in_array($this->user['user_type'], ['admin', 'teacher'], true)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Only admins and teachers may upload images.']);
        }
        $db = db_connect();
        $library = new ImageLibrary($db);
        $transaction = false;
        try {
            ImageLibrary::requireTransactionalTables($db, ['images_library']);
            $files = $this->request->getFileMultiple('images');
            if (!$files || count($files) > 10) {
                throw new PackageValidationException(['Select between 1 and 10 images.']);
            }
            $validated = [];
            $total = 0;
            foreach ($files as $file) {
                if (!$file->isValid() || $file->hasMoved() || $file->getSize() > ImageLibrary::MAX_FILE_BYTES) {
                    throw new PackageValidationException([$file->getClientName() . ': upload failed or the image exceeds 5 MB.']);
                }
                $total += $file->getSize();
                if ($total > 25 * 1024 * 1024) {
                    throw new PackageValidationException(['Upload at most 25 MB of images at a time.']);
                }
                $bytes = file_get_contents($file->getTempName());
                if ($bytes === false) {
                    throw new \RuntimeException('Could not read uploaded image.');
                }
                $name = basename(str_replace('\\', '/', $file->getClientName()));
                if (strlen($name) > 180 || str_contains($name, "\0") || !mb_check_encoding($name, 'UTF-8')) {
                    throw new PackageValidationException(['Image names must be valid UTF-8 and at most 180 bytes.']);
                }
                $validated[] = ImageLibrary::validate($bytes, $name);
            }
            $transaction = $db->transBegin();
            if (!$transaction) {
                throw new \RuntimeException('Could not start image transaction.');
            }
            foreach ($validated as $image) {
                $library->persist($image, (int) $this->user['id']);
            }
            if (!$db->transStatus() || !$db->transCommit()) {
                throw new \RuntimeException('Could not save uploaded images.');
            }
            $transaction = false;
            $library->commitFiles();
            return $this->response->setJSON(['status' => 'success', 'message' => count($validated) . ' image(s) uploaded.', 'count' => count($validated), 'csrfHash' => csrf_hash()]);
        } catch (\Throwable $error) {
            if ($transaction) {
                $db->transRollback();
            }
            $library->rollbackFiles();
            $errors = $error instanceof PackageValidationException ? $error->errors : ['Images could not be saved. Check that the migration is installed and image storage is writable.'];
            log_message('error', 'Image upload failed: {error}', ['error' => $error->getMessage()]);
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'No images were uploaded.', 'errors' => $errors, 'csrfHash' => csrf_hash()]);
        }
    }

    // Public URLs serve validated raster files from private storage; filenames are never supplied by the visitor.
    public function show(string $publicId)
    {
        if (!preg_match('/\A[a-f0-9]{32}\z/', $publicId)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $image = (new ImageLibraryModel())->where('public_id', $publicId)->first();
        if (!$image || !is_file($path = (new ImageLibrary())->path($image))) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return $this->response->setHeader('Content-Type', $image['mime_type'])
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Disposition', 'inline; filename="' . $image['storage_name'] . '"')
            ->setHeader('Cache-Control', 'public, max-age=31536000, immutable')
            ->setBody(file_get_contents($path));
    }
}
