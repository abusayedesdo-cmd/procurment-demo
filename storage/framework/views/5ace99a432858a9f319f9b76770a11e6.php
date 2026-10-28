
<a href="<?php echo e($preview); ?>" target="_blank" rel="noopener" class="btn btn-outline doc-btn">Preview</a>
<details class="dl-menu">
  <summary class="btn btn-outline doc-btn">Download &#9662;</summary>
  <div class="dl-pop">
    <a href="<?php echo e($word); ?>" download>Word (.docx)</a>
    <?php if(!empty($pdf)): ?><a href="<?php echo e($pdf); ?>" download>PDF</a><?php endif; ?>
  </div>
</details>
<?php /**PATH D:\Procument_Pro\Project_procrument\resources\views/cases/_doc-buttons.blade.php ENDPATH**/ ?>