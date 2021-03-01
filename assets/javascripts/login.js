document.querySelector('form').addEventListener('submit', login);

function login(e) {
  e.preventDefault();
  
  if (document.querySelector('#email').value !== '' && document.querySelector('#password').value !== '') {
	  fetch('/api/user/login', {
	    method: 'post',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
			  email: document.querySelector('#email').value,
			  password: document.querySelector('#password').value
			})
	  }).then(r => r.json())
	  .then(json => {
	    if (!json.error) {
	      localStorage.setItem('user', JSON.stringify(json));
	      window.location.href = '/';
	    } else {
	      document.querySelector('.alerts .row').innerHTML = `
		    <div class="col-12">
		      <div class="alert alert-danger" role="alert">
            ` + json.message + `
          </div>
		    </div>
		    `;
	    }
	  });
  }
}

document.querySelector('#not-account').addEventListener('click', e => {
  e.preventDefault();
  window.location.href = '/register';
});

function parseQueryParams() {
  const queryString = new URL(window.location.href).searchParams;
  let queryParams = {};
  
  if (queryString) {
    let queryArray = queryString.toString().split('&');
    let tmp = {};
    for (elem of queryArray) {
      if (elem.indexOf('=')) {
        tmp[elem.split('=')[0]] = (elem.split('=')[1] === '' ? true : elem.split('=')[1]);
      } else {
        tmp[elem] = true;
      }
    }
    queryParams = tmp;
  }
  
  return queryParams;
}

window.addEventListener('load', () => {
  const query = parseQueryParams();
  if (query.passwordUpdated) {
    document.querySelector('.alerts .row').innerHTML = `
		    <div class="col-12 mt-2">
		      <div class="alert alert-success" role="alert">
            Votre mot de passe à été modifié avec succes
          </div>
		    </div>
		    `;
  } else if (query.identError) {
    localStorage.removeItem('user');
    document.querySelector('.alerts .row').innerHTML = `
		    <div class="col-12 mt-2">
		      <div class="alert alert-danger" role="alert">
            ` + (typeof query.identError === "string" ? (query.identError).replace(/\+/g, ' ') : 'Problème d\'identification') + `
          </div>
		    </div>
		    `;
  }
  
  if (localStorage.getItem('user')) {
    window.location.href = '/';
  }
})