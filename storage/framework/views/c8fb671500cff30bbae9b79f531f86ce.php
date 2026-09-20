<?php $__env->startSection('title', $config['label']); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="content-heading">
        <h1><i class="bi <?php echo e($config['icon']); ?> me-2" style="color:var(--brand)"></i><?php echo e($config['label']); ?></h1>
        <p>Manage <?php echo e(strtolower($config['label'])); ?> displayed across your ecommerce store.</p>
    </div>
    <a href="<?php echo e(route('admin.promotions.create', $key)); ?>" class="btn text-white" style="background:var(--brand)"><i class="bi bi-plus-lg me-1"></i>Add new</a>
</div>

<div class="panel p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Promotion</th><th>Offer / Placement</th><th>Schedule</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if($promotion->banner): ?><img src="<?php echo e($promotion->banner->url); ?>" alt="" class="promotion-thumb"><?php else: ?><div class="promotion-thumb"><i class="bi <?php echo e($config['icon']); ?>"></i></div><?php endif; ?>
                            <div><div class="fw-semibold"><?php echo e($promotion->title); ?></div><?php if($promotion->code): ?><code><?php echo e($promotion->code); ?></code><?php endif; ?></div>
                        </div>
                    </td>
                    <td>
                        <?php if($promotion->discount_type): ?><span class="fw-semibold"><?php echo e($promotion->discount_type === 'percent' ? rtrim(rtrim($promotion->discount_value, '0'), '.').'%' : '$'.number_format($promotion->discount_value, 2)); ?></span><?php endif; ?>
                        <?php if($promotion->placement): ?><span class="badge rounded-pill text-bg-light"><?php echo e(str_replace('_', ' ', ucfirst($promotion->placement))); ?></span><?php endif; ?>
                        <?php if($key === 'flash-deals'): ?><div class="small text-muted"><?php echo e(count($promotion->product_ids ?? [])); ?> product(s)</div><?php endif; ?>
                        <?php if($key === 'ads-campaigns' && $promotion->budget): ?><div class="small text-muted">Budget $<?php echo e(number_format($promotion->budget, 2)); ?></div><?php endif; ?>
                    </td>
                    <td><div class="small"><?php echo e($promotion->starts_at?->format('d M Y, h:i A') ?? 'Immediately'); ?></div><div class="small text-muted">to <?php echo e($promotion->ends_at?->format('d M Y, h:i A') ?? 'No end date'); ?></div></td>
                    <td><span class="order-status <?php echo e($promotion->is_active ? 'delivered' : 'cancelled'); ?>"><?php echo e($promotion->is_active ? 'Active' : 'Inactive'); ?></span></td>
                    <td><div class="d-flex justify-content-end gap-2"><a href="<?php echo e(route('admin.promotions.edit', [$key, $promotion])); ?>" class="action-button edit"><i class="bi bi-pencil-square me-1"></i>Edit</a><form method="POST" action="<?php echo e(route('admin.promotions.destroy', [$key, $promotion])); ?>" onsubmit="return confirm('Delete this promotion?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="action-button delete"><i class="bi bi-trash3 me-1"></i>Delete</button></form></div></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5"><div class="text-center py-5 text-muted"><i class="bi <?php echo e($config['icon']); ?> fs-1 d-block mb-2"></i>No <?php echo e(strtolower($config['label'])); ?> created yet.</div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($promotions->hasPages()): ?><div class="p-3 border-top"><?php echo e($promotions->links()); ?></div><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Ecommerce\resources\views\admin\promotions\index.blade.php ENDPATH**/ ?>