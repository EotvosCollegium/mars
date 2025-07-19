function hidePictures() {
    return (localStorage.getItem('hidePictures') === null) ? true : (localStorage.getItem('hidePictures') === 'true');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.hideable-picture').forEach(function(pic) {
        if (!pic.parentElement.querySelector('.hideable-picture-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'hideable-picture-overlay';
            const text = document.createElement('div');
            text.className = 'hideable-picture-label';
            text.textContent = 'Képek letiltva';
            overlay.appendChild(text);
            pic.parentElement.insertBefore(overlay, pic);
        }
    });
    function updatePictures() {
        const pics = document.querySelectorAll('.hideable-picture');
        pics.forEach(function(pic) {
            const overlay = pic.parentElement.querySelector('.hideable-picture-overlay');
            if (pic.nodeName !== 'IMG')
                pic.style.display = hidePictures() ? 'none' : '';
            overlay.style.display = hidePictures() ? '' : 'none';
        });
    }
    function updateButtons() {
        const btns = document.querySelectorAll('.toggle-hideable-pictures');
        btns.forEach(function(btn) {
            if (hidePictures()) {
                btn.innerText = "Képek megjelenítése";
            } else {
                btn.innerText = "Képek elrejtése";
            }
        });
    }
    const btns = document.querySelectorAll('.toggle-hideable-pictures');
    btns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            localStorage.setItem('hidePictures', String(!hidePictures()));
            updatePictures();
            updateButtons();
        });
    });
    updateButtons();
    updatePictures();
});