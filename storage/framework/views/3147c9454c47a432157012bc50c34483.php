<?php ($isEditing = $subcategory->exists); ?>
<?php $__env->startSection('title', $isEditing ? 'Edit Subcategory' : 'Add Subcategory'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap gap-3 justify-content-between align-items-center content-heading mb-4"><div><h1><?php echo e($isEditing ? 'Edit subcategory' : 'Add subcategory'); ?></h1><p>Attach a detailed collection to one or more categories.</p></div><a href="<?php echo e(route('admin.subcategories.index')); ?>" class="btn border px-3 py-2" style="border-color:var(--line)!important">All subcategories</a></div>
<form method="POST" action="<?php echo e($isEditing ? route('admin.subcategories.update', $subcategory) : route('admin.subcategories.store')); ?>"><div class="row g-4"><div class="col-lg-8"><div class="panel"><div class="row g-3"><div class="col-12"><label class="form-label">Subcategory name</label><input name="name" value="<?php echo e(old('name', $subcategory->name)); ?>" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="e.g. Mobile accessories" required><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div class="col-12"><label class="form-label">Categories</label><select name="categories[]" class="js-category-select <?php $__errorArgs = ['categories'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" multiple required><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($category->id); ?>" <?php if(in_array($category->id, old('categories', $subcategory->categories->modelKeys()))): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><div class="form-text">Search and choose one or more parent categories.</div><?php $__errorArgs = ['categories'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div class="col-12"><label class="form-label">Short description <span class="fw-normal" style="color:var(--muted)">(optional)</span></label><textarea name="short_description" class="form-control" rows="4" maxlength="255" placeholder="A concise description for the storefront."><?php echo e(old('short_description', $subcategory->short_description)); ?></textarea></div></div></div></div><div class="col-lg-4"><div class="panel"><div class="d-flex justify-content-between align-items-center mb-3"><div class="panel-heading">Media</div><a href="<?php echo e(route('admin.media.index')); ?>" class="small text-decoration-none" style="color:var(--brand)">Open library</a></div><input id="subcategory-icon-media" type="hidden" name="icon_media_id" value="<?php echo e(old('icon_media_id', $subcategory->icon_media_id)); ?>"><label class="form-label">Icon <span class="fw-normal" style="color:var(--muted)">(optional)</span></label><div class="media-preview mb-2" data-media-preview="subcategory-icon-media"><?php if($subcategory->icon): ?><img src="<?php echo e($subcategory->icon->url); ?>" alt=""><?php else: ?><span><i class="bi bi-image me-1"></i>No icon selected</span><?php endif; ?></div><button type="button" class="btn btn-sm border w-100" data-media-picker-target="subcategory-icon-media" style="border-color:var(--line)!important"><i class="bi bi-images me-1"></i><span data-media-label="subcategory-icon-media"><?php echo e($subcategory->icon?->original_name ?? 'Choose icon'); ?></span></button><p class="small mt-3 mb-0" style="color:var(--muted)">For best results, use a square image with a clear subject.</p></div></div><div class="col-12"><button class="btn text-white px-4 py-2" style="background:var(--brand);border-radius:10px"><i class="bi bi-check2 me-1"></i><?php echo e($isEditing ? 'Update subcategory' : 'Save subcategory'); ?></button></div></div><?php echo csrf_field(); ?> <?php if($isEditing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?></form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Ecommerce\resources\views/admin/subcategories/create.blade.php ENDPATH**/ ?>