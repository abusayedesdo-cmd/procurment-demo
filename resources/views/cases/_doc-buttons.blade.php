{{-- Preview (PDF, new tab) + a Download ▾ menu with Word / PDF. Vars: $preview, $word, $pdf --}}
<a href="{{ $preview }}" target="_blank" rel="noopener" class="btn btn-outline doc-btn">Preview</a>
<details class="dl-menu">
  <summary class="btn btn-outline doc-btn">Download &#9662;</summary>
  <div class="dl-pop">
    <a href="{{ $word }}" download>Word (.docx)</a>
    <a href="{{ $pdf }}" download>PDF</a>
  </div>
</details>
