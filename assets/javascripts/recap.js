document.querySelector('#logout').addEventListener('click', () => {
    localStorage.removeItem('user');
    window.location.href = '/login';
});

window.addEventListener('load', () => {
    const today = new Date();
    const days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    document.querySelector('#today').innerHTML = days[today.getDay()] + ' ' + today.getDate() + ' ' + months[today.getMonth()] + ' ' + today.getFullYear();


    const user = JSON.parse(localStorage.getItem('user'));
    document.querySelector('#profile span').innerHTML = user.lastname + ' ' + user.firstname.substr(0, 1) + '.';
    document.querySelector('#profile span + strong').innerHTML = user.agency;

    if (document.querySelector('editor')) {
        fetch('/views/recap_templates/hebdo.html')
            .then(r => r.text())
            .then(html => {
                $('#editor').trumbowyg({
                    btnsDef: {
                        // Create a new dropdown
                        image: {
                            dropdown: ['insertImage', 'upload'],
                            ico: 'insertImage'
                        }
                    },
                    btns: [
                        ['viewHTML'],
                        ['formatting'],
                        ['strong', 'em', 'del'],
                        ['superscript', 'subscript'],
                        ['link'],
                        ['image'],
                        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                        ['unorderedList', 'orderedList'],
                        ['horizontalRule'],
                        ['removeformat'],
                        ['fullscreen'],
                        ['emoji'],
                        ['template']
                    ],
                    plugins: {
                        upload: {
                            serverPath: '/api/recap/upload',
                            fileFieldName: 'image',
                            headers: {
                                'Authorization': JSON.parse(localStorage.getItem('user')).token
                            },
                            urlPropertyName: 'file'
                        },
                        templates: [
                            { name: 'Récap hebdo', html }
                        ]
                    }
                });
            });
    }

    if (document.querySelector('#send_recap')) {
        document.querySelector('#send_recap').addEventListener('click', e => {
            e.preventDefault()
            e.stopPropagation();

            fetch('/api/recap', {
                    method: 'post',
                    body: JSON.stringify({
                        html: document.querySelector('#editor').innerHTML,
                        token: JSON.parse(localStorage.getItem('user')).token
                    })
                }).then(r => r.json())
                .then(json => {
                    console.log(json);
                });
        });
    }

    if (document.querySelector('#save_template')) {
        document.querySelector('#save_template').addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();

            console.log('coucou');
        });
    }

    if (document.querySelector('.historique-recap-container')) {
        const token = JSON.parse(localStorage.getItem('user')).token;
        fetch(`/api/recaps/${token}`)
            .then(r => r.json())
            .then(json => {
                const template = recap => `
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${new Date(reap.creation_date).getDate()}/${new Date(reap.creation_date).getMonth() + 1}/${new Date(reap.creation_date).getFullYear()}</h5>
                                <p class="card-text">${recap.object}</p>
                                <a href="/recap?id=${recap.id}" class="btn btn-primary">Voir plus</a>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.querySelector('.historique-recap-container').innerHTML = '';
                for (let recap of json) {
                    document.querySelector('.histique-recap-list').innerHTML += template(recap);
                }
            })
    }
});