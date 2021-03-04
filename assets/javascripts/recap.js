

window.addEventListener('load', () => {
    const today = new Date();
    const days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    document.querySelector('#today').innerHTML = days[today.getDay()] + ' ' + today.getDate() + ' ' + months[today.getMonth()] + ' ' + today.getFullYear();

  
    const user = JSON.parse(localStorage.getItem('user'));
    document.querySelector('#profile span').innerHTML = user.lastname + ' ' + user.firstname.substr(0, 1) + '.';
    document.querySelector('#profile span + strong').innerHTML = user.agency;

    

    $('#editor').trumbowyg({
        btnsDef: {
            // Create a new dropdown
            image: {
                dropdown: ['insertImage', 'upload'],
                ico: 'insertImage'
            }
        },
        // Redefine the button pane
        btns: [
            ['viewHTML'],
            ['formatting'],
            ['strong', 'em', 'del'],
            ['superscript', 'subscript'],
            ['link'],
            ['image'], // Our fresh created dropdown
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen'],
            ['emoji']
        ],
        plugins: {
            // Add imagur parameters to upload plugin for demo purposes
            upload: {
                serverPath: '/api/recap/upload',
                fileFieldName: 'image',
                headers: {
                    'Authorization': JSON.parse(localStorage.getItem('user')).token
                },
                urlPropertyName: 'file'
            }
        }
    });
});