var btColl = document.getElementsByClassName("collapsible");
for (var btIter = 0; btIter < btColl.length; i++) {
    btColl[btIter].addEventListener("click", function() {
        document.getElementsByClassName('bt-content')[0].style.display = 'block';
        document.getElementsByClassName('bt-warning')[0].style.display = 'none';
    });
}