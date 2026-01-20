<?php
/**
 * MR HASAR DANIŞMANLIK - Yetki Yönetimi
 */
if (!hasRole('admin')) { header('Location: index.php'); exit; }
$permissions = ['dosya_goruntule','dosya_ekle','dosya_duzenle','dosya_sil','crm_goruntule','crm_ekle','kasa_goruntule','kasa_islem','rapor_goruntule','ayar_duzenle','kullanici_yonetimi'];
$roles = ['user'=>'Kullanıcı','lawyer'=>'Avukat','manager'=>'Yönetici','admin'=>'Admin'];
?>
<div class="page-header"><h1><i class="bi bi-shield-lock me-2"></i>Yetki Yönetimi</h1></div>
<div class="card"><div class="card-body">
<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Admin rolü tüm yetkilere sahiptir.</div>
<table class="table"><thead><tr><th>Yetki</th><?php foreach($roles as $r=>$l):?><th class="text-center"><?=$l?></th><?php endforeach;?></tr></thead>
<tbody><?php foreach($permissions as $p):?><tr><td><?=ucwords(str_replace('_',' ',$p))?></td>
<?php foreach($roles as $r=>$l):?><td class="text-center">
<?php if($r==='admin'):?><i class="bi bi-check-circle-fill text-success"></i>
<?php else:?><input type="checkbox" class="form-check-input"><?php endif;?>
</td><?php endforeach;?></tr><?php endforeach;?></tbody></table>
<button class="btn btn-primary mt-3"><i class="bi bi-save me-1"></i>Kaydet</button>
</div></div>
