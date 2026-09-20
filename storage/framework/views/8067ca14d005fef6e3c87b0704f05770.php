<?php ($editing = $promotion->exists); ?>
<?php $__env->startSection('title', ($editing ? 'Edit ' : 'Add ').$config['label']); ?>
<?php $__env->startSection('content'); ?>
<div class="content-heading mb-4">
    <h1><i class="bi <?php echo e($config['icon']); ?> me-2" style="color:var(--brand)"></i><?php echo e($editing ? 'Edit' : 'Add'); ?> <?php echo e($config['label']); ?></h1>
    <p>Configure the content, schedule, and storefront behavior.</p>
</div>

<form method="POST" action="<?php echo e($editing ? route('admin.promotions.update', [$key, $promotion]) : route('admin.promotions.store', $key)); ?>">
    <?php echo csrf_field(); ?>
    <?php if($editing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="panel">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">Title <span class="text-danger">*</span></label><input name="title" value="<?php echo e(old('title', $promotion->title)); ?>" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>

                    <?php if($key === 'promo-codes'): ?>
                        <div class="col-md-6"><label class="form-label">Promo code <span class="text-danger">*</span></label><input name="code" value="<?php echo e(old('code', $promotion->code)); ?>" class="form-control text-uppercase <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="SAVE20" required><?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <?php endif; ?>

                    <?php if($key === 'flash-deals'): ?>
                        <div class="col-12"><label class="form-label">Products <span class="text-danger">*</span></label><select name="product_ids[]" class="form-select js-product-select <?php $__errorArgs = ['product_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" multiple><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($product->id); ?>" <?php if(in_array($product->id, old('product_ids', $promotion->product_ids ?? []))): echo 'selected'; endif; ?>><?php echo e($product->name); ?><?php echo e($product->sku ? ' · '.$product->sku : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><?php $__errorArgs = ['product_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <?php endif; ?>

                    <?php if(in_array($key, ['flash-deals', 'promo-codes'])): ?>
                        <div class="col-md-6"><label class="form-label">Discount type <span class="text-danger">*</span></label><select name="discount_type" class="form-select" required><option value="percent" <?php if(old('discount_type', $promotion->discount_type) === 'percent'): echo 'selected'; endif; ?>>Percentage</option><option value="amount" <?php if(old('discount_type', $promotion->discount_type) === 'amount'): echo 'selected'; endif; ?>>Fixed amount</option></select></div>
                        <div class="col-md-6"><label class="form-label">Discount value <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" name="discount_value" value="<?php echo e(old('discount_value', $promotion->discount_value)); ?>" class="form-control <?php $__errorArgs = ['discount_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required><?php $__errorArgs = ['discount_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <?php endif; ?>

                    <?php if($key === 'promo-codes'): ?>
                        <div class="col-md-6"><label class="form-label">Minimum order amount</label><input type="number" step="0.01" min="0" name="minimum_order_amount" value="<?php echo e(old('minimum_order_amount', $promotion->minimum_order_amount)); ?>" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Usage limit</label><input type="number" min="1" name="usage_limit" value="<?php echo e(old('usage_limit', $promotion->usage_limit)); ?>" class="form-control" placeholder="Leave empty for unlimited"></div>
                    <?php endif; ?>

                    <?php if(in_array($key, ['banners', 'ads-campaigns'])): ?>
                        <div class="col-md-6"><label class="form-label">Placement <span class="text-danger">*</span></label><select name="placement" class="form-select" required><?php $__currentLoopData = ['home_hero'=>'Home hero','home_middle'=>'Home middle','sidebar'=>'Sidebar','checkout'=>'Checkout']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('placement', $promotion->placement) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                        <div class="col-md-6"><label class="form-label">Destination URL</label><input type="url" name="link_url" value="<?php echo e(old('link_url', $promotion->link_url)); ?>" class="form-control <?php $__errorArgs = ['link_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="https://example.com/collection"><?php $__errorArgs = ['link_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <?php endif; ?>

                    <?php if($key === 'ads-campaigns'): ?>
                        <div class="col-md-6"><label class="form-label">Campaign budget</label><input type="number" step="0.01" min="0" name="budget" value="<?php echo e(old('budget', $promotion->budget)); ?>" class="form-control"></div>
                    <?php endif; ?>

                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" rows="5" class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $promotion->description)); ?></textarea><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="col-md-6"><label class="form-label">Starts at</label><input type="datetime-local" name="starts_at" value="<?php echo e(old('starts_at', $promotion->starts_at?->format('Y-m-d\TH:i'))); ?>" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Ends at</label><input type="datetime-local" name="ends_at" value="<?php echo e(old('ends_at', $promotion->ends_at?->format('Y-m-d\TH:i'))); ?>" class="form-control <?php $__errorArgs = ['ends_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php $__errorArgs = ['ends_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <?php if(in_array($key, ['banners', 'ads-campaigns'])): ?>
                <div class="panel mb-4">
                    <div class="panel-heading mb-2">Campaign image <span class="text-danger">*</span></div>
                    <p class="small text-muted">Choose a wide, optimized image from the Media Library.</p>
                    <div class="media-preview promotion-media-preview mb-3" data-media-preview="promotion-banner"><?php if($promotion->banner): ?><img src="<?php echo e($promotion->banner->url); ?>" alt="<?php echo e($promotion->title); ?>"><?php else: ?><i class="bi bi-image fs-1"></i><?php endif; ?></div>
                    <input type="hidden" id="promotion-banner" name="banner_media_id" value="<?php echo e(old('banner_media_id', $promotion->banner_media_id)); ?>">
                    <button type="button" class="btn btn-outline-primary w-100" data-media-picker-target="promotion-banner"><i class="bi bi-images me-1"></i>Choose image</button>
                    <?php $__errorArgs = ['banner_media_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-2"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            <?php endif; ?>
            <div class="panel">
                <div class="panel-heading mb-3">Availability</div>
                <div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" role="switch" id="promotion-status" name="is_active" value="1" <?php if(old('is_active', $promotion->is_active)): echo 'checked'; endif; ?>><label class="form-check-label" for="promotion-status">Active</label></div>
                <p class="small text-muted mb-0 mt-2">Inactive promotions remain saved but are hidden from the storefront.</p>
            </div>
        </div>
    </div>
    <div class="mt-4"><a href="<?php echo e(route('admin.promotions.index', $key)); ?>" class="btn btn-light">Cancel</a><button class="btn text-white ms-2" style="background:var(--brand)"><?php echo e($editing ? 'Update' : 'Save'); ?> <?php echo e(strtolower($config['label'])); ?></button></div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Ecommerce\resources\views\admin\promotions\form.blade.php ENDPATH**/ ?>