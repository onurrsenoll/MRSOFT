<?php
/**
 * MR HASAR DANIŞMANLIK - İhbar Föyü
 * HERZAMAN FARKEDER
 */

// Liste
$stmt = $pdo->query("SELECT * FROM ihbar_foyleri ORDER BY created_at DESC LIMIT 50");
$foiler = $stmt->fetchAll();
?>
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-file-earmark-text me-2"></i>İhbar Föyü</h1>
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#foyModal">
            <i class="bi bi-plus-lg me-1"></i> Yeni Föy
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Föy No</th>
                        <th>Müvekkil</th>
                        <th>Kaza Tarihi</th>
                        <th>Hasar Tipi</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($foiler)): ?>
                    <tr><td colspan="7" class="text-center text-muted">Kayıt bulunamadı</td></tr>
                    <?php else: ?>
                    <?php foreach ($foiler as $f): ?>
                    <tr>
                        <td><strong><?= e($f['foyu_no']) ?></strong></td>
                        <td><?= e($f['muvekkil_ad']) ?></td>
                        <td><?= formatDate($f['kaza_tarihi']) ?></td>
                        <td><span class="badge bg-primary"><?= e($f['hasar_tipi']) ?></span></td>
                        <td>
                            <span class="badge bg-<?= $f['durum'] === 'TAMAMLANDI' ? 'success' : 'warning' ?>">
                                <?= e($f['durum']) ?>
                            </span>
                        </td>
                        <td><?= formatDate($f['created_at']) ?></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="foyModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni İhbar Föyü</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">İhbar föyü detaylı form burada yer alacak...</p>
            </div>
        </div>
    </div>
</div>
