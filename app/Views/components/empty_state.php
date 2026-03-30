<div class="empty-state text-center p-4">
  <i class="fa-regular <?= e((string)($icon ?? 'fa-folder-open')) ?> fs-1 mb-3 text-secondary"></i>
  <h6 class="mb-1"><?= e((string)($title ?? 'Sem dados disponíveis')) ?></h6>
  <p class="text-muted mb-0"><?= e((string)($message ?? 'Tente ajustar os filtros ou voltar mais tarde.')) ?></p>
</div>
