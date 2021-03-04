

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

    fetch('/views/recap_templates/hebdo.html')
        .then(r => r.text())
        .then(html => {
            console.log(html.replace(/\n/g, '')
                                                            .replace(/\>[\ ]+\</g, '><')
                                                            .replace(/\>[\ ]+(.)/g, '>$1')
                                                            .replace(/(.)[\ ]+\</g, '$1<')
                                                            .replace(/(.+)\<([a-zA-Z])/g, '$1 <$2')
                                                            .replace(/([a-zA-Z])\>(.+)\<\/([a-zA-Z])/g, '$1> $2 </$3')
                                                            .replace(/\/([a-zA-Z])\>(.+)/g, '/$1> $2')
                                                            .replace(/\"\>(.+)/g, '"> $1')
                                                            .replace(/\<\/([a-zA-Z])\>(.+)/g, '</$1> $2'));

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
                        { name: 'Récap hebdo', html: html.replace(/\n/g, '')
                                                            .replace(/\>[\ ]+\</g, '><')
                                                            .replace(/\>[\ ]+(.)/g, '>$1')
                                                            .replace(/(.)[\ ]+\</g, '$1<')
                                                            .replace(/(.+)\<([a-zA-Z])/g, '$1 <$2')
                                                            .replace(/([a-zA-Z])\>(.+)\<\/([a-zA-Z])/g, '$1> $2 </$3')
                                                            .replace(/\/([a-zA-Z])\>(.+)/g, '/$1> $2')
                                                            .replace(/\"\>(.+)/g, '"> $1')
                                                            .replace(/\<\/([a-zA-Z])\>(.+)/g, '</$1> $2') }
                    ]
                }
            });
        });
});