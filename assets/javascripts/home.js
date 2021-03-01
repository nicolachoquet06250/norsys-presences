if (!localStorage.getItem('user')) {
  window.location.href = '/login';
}

function write_table(json) {
  if (json.length !== 0) {
    let tbody = '';
    for (let line of json) {
      let date = new Date(line.arrival_date);
      let arrival = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':' + (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes());
      let departure = line.departure_date !== null ? 
        (parseInt((new Date(line.departure_date)).getHours()) < 10 ? '0' + (new Date(line.departure_date)).getHours() : (new Date(line.departure_date)).getHours()) + ':' + (parseInt((new Date(line.departure_date)).getMinutes()) < 10 ? '0' + (new Date(line.departure_date)).getMinutes() : (new Date(line.departure_date)).getMinutes()) : '';
      
      tbody += `<tr>
        <td>` + (date.getDate() < 10 ? '0' : '') + date.getDate() + '/' + (date.getMonth() + 1 < 10 ? '0' : '') + (date.getMonth() + 1) + '/' + date.getFullYear() + `</td>
        <td>` + line.lastname + ' ' + line.firstname.substr(0, 1) + `.</td>
        <td>` + arrival  + `</td>
        <td>` + departure + `</td>
      </tr>`;
      
      if (line.firstname === JSON.parse(localStorage.getItem('user')).firstname && line.lastname === JSON.parse(localStorage.getItem('user')).lastname) {
        document.querySelector('#arrival').classList.add('btn-disabled');
        document.querySelector('#arrival').setAttribute('disabled', 'disabled');
      
        
        document.querySelector('#departure').classList.remove('btn-disabled');
        document.querySelector('#departure').removeAttribute('disabled');
      
        if (departure) {
          document.querySelector('#departure').classList.add('btn-disabled');
          document.querySelector('#departure').setAttribute('disabled', 'disabled');
        }
      }
    }
    
    document.querySelector('tbody').innerHTML = tbody;
  }
}

function request_and_write_table() {
  fetch('/api/presences/today?token='+JSON.parse(localStorage.getItem('user')).token, {
    method: 'get'
  }).then(r => r.json())
  .then(json => {
    if (json.error && json.authent) {
      window.location.href = '/login?identError=' + json.message;
    } else {
      write_table(json);
    }
  });
}

document.querySelector('#logout').addEventListener('click', () => {
  localStorage.removeItem('user');
  window.location.href = '/login';
});

document.querySelector('#profile').addEventListener('click', e => {
  if (e.target.getAttribute('id') === 'logout' || e.target.tagName === 'I') {
    e.preventDefault();
  }
});

document.querySelector('#arrival').addEventListener('click', () => {
  fetch('/api/presence', {
    method: 'post',
    body: JSON.stringify({
      type: 'arrival',
      user_id: JSON.parse(localStorage.getItem('user')).id
    })
  }).then(r => r.json())
  .then(json => {
    if (!json.error) {
      request_and_write_table();
    }
  });
});
document.querySelector('#departure').addEventListener('click', () => {
  fetch('/api/presence', {
    method: 'post',
    body: JSON.stringify({
      type: 'departure',
      user_id: JSON.parse(localStorage.getItem('user')).id
    })
  }).then(r => r.json())
  .then(json => {
    if (!json.error) {
      request_and_write_table();
    }
  });
});

Array.from(document.querySelectorAll('.toggle-password')).map(e => e.addEventListener('click', _e => {
  _e.preventDefault();
  if (e.classList.contains('fa-eye')) {
	  e.classList.remove('fa-eye');
	  e.classList.add('fa-eye-slash');
  } else {
    e.classList.remove('fa-eye-slash');
	  e.classList.add('fa-eye');
  }
  e.previousElementSibling.setAttribute('type', (e.previousElementSibling.getAttribute('type') === 'password' ? 'text' : 'password'));
}));

document.querySelector('form').addEventListener('submit', e => {
  e.preventDefault();
  
  function check_passwords_validity() {
    return document.querySelector('#password-1').value === document.querySelector('#password-2').value;
  }
  
  if (!check_passwords_validity()) {
    document.querySelector('#password-2').classList.add('is-invalid');
    document.querySelector('.modal-alerts').innerHTML = `
		    <div class="alert alert-danger" role="alert">
          Les 2 mots de passes ne sont pas identiques
        </div>
		    `;
  } else {
    fetch('/api/user/password', {
       method: 'put',
       body: JSON.stringify({
         user_id: JSON.parse(localStorage.getItem('user')).id,
         password: document.querySelector('#password-1').value
       })
    }).then(r => r.json())
    .then(json => {
      if (json.error) {
        document.querySelector('#password-2').classList.remove('is-valid');
		    document.querySelector('#password-2').classList.add('is-invalid');
		    
        document.querySelector('#password-1').classList.remove('is-valid');
		    document.querySelector('#password-1').classList.add('is-invalid');
		    
		    
        document.querySelector('.modal-alerts').innerHTML = `
		    <div class="alert alert-danger" role="alert">
          ` + json.message + `
        </div>
		    `;
      } else {
        document.querySelector('#password-2').classList.remove('is-invalid');
		    document.querySelector('#password-2').classList.add('is-valid');
		    
		    let error_alert;
		    if (error_alert = document.querySelector('.modal-alerts .alert')) {
		      error_alert.remove();
		    }
		    
		    localStorage.removeItem('user');
	      window.location.href = '/login?passwordUpdated';
      }
    });
  }
});

document.querySelector('#pdf-export').addEventListener('click', () =>  {
  const date = document.querySelector('.norsys-dropdown ul li.active a') ? document.querySelector('.norsys-dropdown ul li.active a').getAttribute('data-date') : ((new Date()).getFullYear() + '-' + ((new Date()).getMonth() < 10 ? '0' : '') + (new Date()).getMonth() + '-' + ((new Date().getDate()) < 10 ? '0' : '') + (new Date()).getDate());
  fetch('/api/export/' + date, {
    method: 'post',
    body: JSON.stringify({
      email: JSON.parse(localStorage.getItem('user')).email
    })
  }).then(r => r.json())
  .then(json => {
    if (json.error) {
        document.querySelector('.page-alerts').innerHTML = `
		    <div class="alert alert-danger" role="alert">
          ` + json.message + `
        </div>
		    `;
    } else {
        document.querySelector('.page-alerts').innerHTML = `
		    <div class="alert alert-success" role="alert">
          Un email vous à été envoyé avec l'export en pièce jointe
        </div>
		    `;
    }
    const timeout = setTimeout(() => {
      document.querySelector('.page-alerts .alert').remove();
      clearTimeout(timeout);
    }, 3000);
  })
})

Array.from(document.querySelectorAll('.dropdown-toggle')).map(d => d.addEventListener('click', e => {
  e.preventDefault();
  document.querySelector('ul.dropdown-menu[aria-labelledby="' + d.getAttribute('id') + '"]').classList.toggle('show');
}));

window.addEventListener('load', () => {
  const today = new Date();
  const days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
  const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
  document.querySelector('#today').innerHTML = days[today.getDay()] + ' ' + today.getDate() + ' ' + months[today.getMonth()] + ' ' + today.getFullYear();
  
  request_and_write_table();
  
  const user = JSON.parse(localStorage.getItem('user'));
  document.querySelector('#profile span').innerHTML = user.lastname + ' ' + user.firstname.substr(0, 1) + '.';
  document.querySelector('#profile span + strong').innerHTML = user.agency;
  

  fetch('/api/search/history', {
    method: 'get'
  }).then(r => r.json())
  .then(json => {
    for (let date of json) {
      let _date = new Date(date);
      _date = days[_date.getDay()] + ' ' + _date.getDate() + ' ' + months[_date.getMonth()] + ' ' + _date.getFullYear();
      
      let today = new Date();
      
      if (date === (today.getFullYear() + '-' + (parseInt(today.getMonth() + 1) < 10 ? '0' : '') + parseInt(today.getMonth() + 1) + '-' + (parseInt(today.getDate()) < 10 ? '0' : '') + today.getDate())) {
        _date = 'Aujourd\'hui';
      }
      
      const li = document.createElement('li');
      if (_date === 'Aujourd\'hui') {
        li.classList.add('active');
      }
      
      const a = document.createElement('a');
      a.classList.add('dropdown-item');
      a.setAttribute('href', '#');
      a.setAttribute('data-date', date);
      a.innerHTML = _date;
      
      a.addEventListener('click', e => {
        e.preventDefault();
        
        fetch('/api/search/history/' + a.getAttribute('data-date'), {
          method: 'get'
        }).then(r => r.json())
        .then(json => {
          write_table(json)
          document.querySelector('.norsys-dropdown .norsys-dropdown-content li.active').classList.remove('active');
          a.parentElement.classList.add('active');
          document.querySelector('.norsys-dropdown input[type="checkbox"]').checked = false;
          
          let current_date = new Date(a.getAttribute('data-date'));
          
          document.querySelector('#today').innerHTML = days[current_date.getDay()] + ' ' + current_date.getDate() + ' ' + months[current_date.getMonth()] + ' ' + current_date.getFullYear();
        })
      })
      
      li.appendChild(a);
      
      document.querySelector('.norsys-dropdown .norsys-dropdown-content').appendChild(li);
    }
  })
});