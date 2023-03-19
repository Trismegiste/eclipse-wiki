https://krasjet.com/voice/pdf.tocgen/#a-worked-example 

   25  apt-get install pip
   26  pip install -U pdf.tocgen


   66  pdfxmeta -p 1 -a 1 Eclipse-Savage-Book.pdf "^Eclipse Phase" >> receipe.toml
   67  pdfxmeta -p 1 -a 1 Eclipse-Savage-Book.pdf "^Commencement" >> receipe.toml
   68  pdfxmeta -p 1 -a 2 Eclipse-Savage-Book.pdf "^Cadre de" >> receipe.toml


   55  pdftocgen Eclipse\ Savage.pdf < receipe.toml > toc.txt
   56  pdftocio Eclipse\ Savage.pdf < toc.txt 
 