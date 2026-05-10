document.addEventListener('click', function (event) {
    const button = event.target.closest('.cart-action');

    if (!button) {
        return;
    }

    event.preventDefault();

    fetch(button.dataset.url, {
        method: 'POST',
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {
        document.querySelector('#cart-content').innerHTML = data.html;

        // Met à jour le badge du nombre d'articles dans le panier.
        document.querySelector('#cart-count').textContent = data.totalQuantity;
    });
});
