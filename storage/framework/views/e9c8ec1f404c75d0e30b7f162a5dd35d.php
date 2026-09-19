<?php ($isEditing = $category->exists); ?>
<?php $__env->startSection('title', $isEditing ? 'Edit Category' : 'Add Category'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap gap-3 justify-content-between align-items-center content-heading mb-4"><div><h1><?php echo e($isEditing ? 'Edit category' : 'Add category'); ?></h1><p><?php echo e($isEditing ? 'Update this storefront collection.' : 'Create a main storefront collection.'); ?></p></div><a href="<?php echo e(route('admin.categories.index')); ?>" class="btn border px-3 py-2" style="border-color:var(--line)!important">All categories</a></div>
<form method="POST" action="<?php echo e($isEditing ? route('admin.categories.update', $category) : route('admin.categories.store')); ?>"><div class="row g-4"><div class="col-lg-8"><div class="panel"><div class="row g-3"><div class="col-md-8"><label class="form-label">Category name</label><input name="name" value="<?php echo e(old('name', $category->name)); ?>" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="e.g. Electronics" required><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div class="col-md-4"><label class="form-label">Display order</label><input type="number" min="0" name="display_order" value="<?php echo e(old('display_order', $category->display_order)); ?>" class="form-control <?php $__errorArgs = ['display_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required></div><div class="col-12"><label class="form-label">Status</label><div class="d-flex gap-4"><label class="form-check"><input class="form-check-input" type="radio" name="is_active" value="1" <?php if(old('is_active', $category->is_active) == 1): echo 'checked'; endif; ?>><span class="form-check-label">Active</span></label><label class="form-check"><input class="form-check-input" type="radio" name="is_active" value="0" <?php if(old('is_active', $category->is_active) == 0): echo 'checked'; endif; ?>><span class="form-check-label">Inactive</span></label></div></div></div></div></div><div class="col-lg-4"><div class="panel"><div class="d-flex justify-content-between align-items-center mb-3"><div class="panel-heading">Media</div><a href="<?php echo e(route('admin.media.index')); ?>" class="small text-decoration-none" style="color:var(--brand)">Open library</a></div><input id="category-icon-media" type="hidden" name="icon_media_id" value="<?php echo e(old('icon_media_id', $category->icon_media_id)); ?>"><label class="form-label">Icon</label><div class="media-preview mb-2" data-media-preview="category-icon-media"><?php if($category->icon): ?><img src="<?php echo e($category->icon->url); ?>" alt=""><?php else: ?><span><i class="bi bi-image me-1"></i>No icon selected</span><?php endif; ?></div><button type="button" class="btn btn-sm border w-100 mb-3" data-media-picker-target="category-icon-media" style="border-color:var(--line)!important"><i class="bi bi-images me-1"></i><span data-media-label="category-icon-media"><?php echo e($category->icon?->original_name ?? 'Choose icon'); ?></span></button><input id="category-banner-media" type="hidden" name="banner_media_id" value="<?php echo e(old('banner_media_id', $category->banner_media_id)); ?>"><label class="form-label">Banner</label><div class="media-preview mb-2" data-media-preview="category-banner-media"><?php if($category->banner): ?><img src="<?php echo e($category->banner->url); ?>" alt=""><?php else: ?><span><i class="bi bi-card-image me-1"></i>No banner selected</span><?php endif; ?></div><button type="button" class="btn btn-sm border w-100" data-media-picker-target="category-banner-media" style="border-color:var(--line)!important"><i class="bi bi-images me-1"></i><span data-media-label="category-banner-media"><?php echo e($category->banner?->original_name ?? 'Choose banner'); ?></span></button></div></div><div class="col-12"><button class="btn text-white px-4 py-2" style="background:var(--brand);border-radius:10px"><i class="bi bi-check2 me-1"></i><?php echo e($isEditing ? 'Update category' : 'Save category'); ?></button></div></div><?php echo csrf_field(); ?> <?php if($isEditing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?></form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Ecommerce\resources\views/admin/categories/create.blade.php ENDPATH**/ ?>