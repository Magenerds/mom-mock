var pathname = window.location.pathname.split('/'),
    id = pathname[1];
id = (id) ? id : 'order';
document.getElementById(id).setAttribute('class', 'active')