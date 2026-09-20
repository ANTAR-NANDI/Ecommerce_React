<div class="row row-cols-2 row-cols-md-3 g-3">
    <?php $__empty_1 = true; $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col"><button type="button" class="media-choice" data-select-media="<?php echo e($item->id); ?>" data-media-url="<?php echo e($item->url); ?>" data-media-name="<?php echo e(e($item->original_name)); ?>"><img src="<?php echo e($item->url); ?>" alt="<?php echo e($item->original_name); ?>"><div class="p-2 media-file-name text-truncate"><?php echo e($item->original_name); ?></div></button></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12 text-center py-5" style="color:var(--muted)"><i class="bi bi-images fs-2 d-block mb-2"></i>No images found. Upload one in Media Library first.</div>
    <?php endif; ?>
</div>
<?php if($media->hasPages()): ?>
    <nav class="mt-4 d-flex justify-content-center" aria-label="Media pages"><ul class="pagination pagination-sm mb-0">
        <?php $__currentLoopData = $media->linkCollection(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="page-item <?php echo e($link['active'] ? 'active' : ''); ?> <?php echo e($link['url'] ? '' : 'disabled'); ?>"><a class="page-link" data-media-page href="<?php echo e($link['url'] ?? '#'); ?>"><?php echo $link['label']; ?></a></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul></nav>
<?php endif; ?>
<?php /**PATH F:\laragon\www\Ecommerce\resources\views\admin\media\partials\picker.blade.php ENDPATH**/ ?>