{{-- ESDO letterhead pad (ESDO_PAD_Formatin.pdf): PDF page margins + the pad drawn behind every page.
     Margins: top 33 mm, right 10 mm, bottom 33 mm, left 30 mm.
     Pad measurements (A4): header ends at ~30 mm from the top, footer starts at ~269 mm.
     The background image is offset by the top/left margin so it always covers the whole page —
     if you change the margins above, change top/left below to the same values. --}}
@page { margin: 33mm 10mm 33mm 30mm; }
.esdo-pad-bg { position: fixed; top: -33mm; left: -30mm; width: 210mm; height: 297mm; z-index: -1; }
