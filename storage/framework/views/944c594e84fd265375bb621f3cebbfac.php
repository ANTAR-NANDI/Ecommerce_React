<?php ($label = ucfirst($type)); ?>
<?php ($routeName = $type.'s'); ?>
<?php ($editing = $item->exists); ?>
<?php $__env->startSection('title',($editing ? 'Edit ' : 'Add ').$label); ?>
<?php $__env->startSection('content'); ?>
<div class="content-heading mb-4"><h1><?php echo e($editing ? 'Edit '.$label : 'Add '.$label); ?></h1><p>Set the name and storefront visibility.</p></div><form method="POST" action="<?php echo e($editing ? route('admin.'.$routeName.'.update',$item) : route('admin.'.$routeName.'.store')); ?>"><?php echo csrf_field(); ?> <?php if($editing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><div class="panel" style="max-width:620px"><label class="form-label"><?php echo e($label); ?> name</label><input name="name" value="<?php echo e(old('name',$item->name)); ?>" class="form-control" required><div class="mt-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?php if(old('is_active',$item->is_active)): echo 'checked'; endif; ?>> Active</label></div><button class="btn text-white mt-4" style="background:var(--brand)"><?php echo e($editing ? 'Update '.$label : 'Save '.$label); ?></button></div></form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Ecommerce\resources\views/admin/variants/simples/form.blade.php ENDPATH**/ ?>