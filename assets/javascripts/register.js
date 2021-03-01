if (localStorage.getItem('user')) {
  window.location.href = '/';
}
document.querySelector('form').addEventListener('submit', add_user);

function add_user(e) {
	e.preventDefault();

  if (document.querySelector('#password-1').value === document.querySelector('#password-2').value) {
		fetch('/api/user/register', {
			method: 'post',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				firstname: document.querySelector('#firstname').value,
				lastname: document.querySelector('#lastname').value,
				password: document.querySelector('#password-1').value,
				agency: document.querySelector('#agency').value
			})
		}).then(r => r.json())
		.then(json => {
		  if (json.error) {
		    document.querySelector('.alerts .row').innerHTML = `
		    <div class="col-12">
		      <div class="alert alert-danger" role="alert">
            ` + json.message + `
          </div>
		    </div>
		    `;
		  } else {
		    window.location.href = '/login';
		  }
		});
	} else {
	  document.querySelector('.alerts .row').innerHTML = `
		    <div class="col-12">
		      <div class="alert alert-danger" role="alert">
            Les 2 mots de passes ne sont pas identiques
          </div>
		    </div>
		    `;
	}
}

document.querySelector('#alereay-account').addEventListener('click', e => {
  e.preventDefault();
  window.location.href = '/login';
});

/*window.addEventListener('load', () => {
  fetch('/api/agencies', {
    method: 'get'
  }).then(r => r.json())
  .then(json => {
    document.querySelector('#agency').innerHTML = `
    <option selected>Agence</option>
    `;
    for (let agency of json) {
      document.querySelector('#agency').innerHTML += `
      <option value="` + agency.id + `">` + agency.name + `</option>
      `;
    }
  })
})*/