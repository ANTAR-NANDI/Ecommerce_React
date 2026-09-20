<?php ($editing=$brand->exists); ?>
<?php $__env->startSection('title',$editing ? 'Edit Brand' : 'Add Brand'); ?>
<?php $__env->startSection('content'); ?>
<div class="content-heading mb-4"><h1><?php echo e($editing ? 'Edit brand' : 'Add brand'); ?></h1><p>Add a brand name, icon, and visibility status.</p></div><form method="POST" action="<?php echo e($editing ? route('admin.brands.update',$brand) : route('admin.brands.store')); ?>"><?php echo csrf_field(); ?> <?php if($editing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><div class="row g-4"><div class="col-lg-7"><div class="panel"><label class="form-label">Brand name</label><input name="name" value="<?php echo e(old('name',$brand->name)); ?>" class="form-control" required><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><div class="mt-4"><label class="form-label">Status</label><label class="form-check ms-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?php if(old('is_active',$brand->is_active)): echo 'checked'; endif; ?>> Active</label></div></div></div><div class="col-lg-5"><div class="panel"><label class="form-label">Brand icon</label><input id="brand-icon-media" type="hidden" name="icon_media_id" value="<?php echo e(old('icon_media_id',$brand->icon_media_id)); ?>"><div class="media-preview mb-2" data-media-preview="brand-icon-media"><?php if($brand->icon): ?><img src="<?php echo e($brand->icon->url); ?>" alt=""><?php else: ?> <span>No icon selected</span><?php endif; ?></div><button type="button" class="btn border w-100" data-media-picker-target="brand-icon-media"><i class="bi bi-images me-1"></i><span data-media-label="brand-icon-media"><?php echo e($brand->icon?->original_name ?? 'Choose icon'); ?></span></button></div></div><div class="col-12"><button class="btn text-white" style="background:var(--brand)"><?php echo e($editing ? 'Update brand' : 'Save brand'); ?></button></div></div></form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Ecommerce\resources\views\admin\variants\brands\form.blade.php ENDPATH**/ ?>