let userId = 0;
let firstName = "";
let lastName = "";

//login functionality
function login()
{
	userId = 0;
	firstName = "";
	lastName = "";
	
	let login = document.getElementById("username").value;
	let password = document.getElementById("password").value;
//	var hash = md5( password );
	
	//document.getElementById("loginResult").innerHTML = "";

	let tmp = {username:login,password:password};
//	var tmp = {login:login,password:hash};
	let jsonPayload = JSON.stringify( tmp );
	
	let url = 'api/login.php';

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				let jsonObject = JSON.parse( xhr.responseText );
                console.log(jsonObject)
				userId = jsonObject.id;
		
				if( userId < 1 )
				{	
                    console.log(userId)
					return;
				}
		
				firstName = jsonObject.first_name;
				lastName = jsonObject.last_name;

				saveCookie();
	
				window.location.href = "profile.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		//document.getElementById("loginResult").innerHTML = err.message;
        alert(err.message)
	}

}


function saveCookie()
{
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime()+(minutes*60*1000));	
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}


function readCookie()
{
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for(var i = 0; i < splits.length; i++) 
	{
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "firstName" )
		{
			firstName = tokens[1];
		}
		else if( tokens[0] == "lastName" )
		{
			lastName = tokens[1];
		}
		else if( tokens[0] == "userId" )
		{
			userId = parseInt( tokens[1].trim() );
		}
	}
	
	if( userId < 0 )
	{
		window.location.href = "index.html";
	}
	else
	{
//		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
	}
}

//logout
function logout()
{
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

//add color function 
function addColor()
{
	let button = document.querySelector("#add-color-button")
	button.addEventListener("click", async()=> {
		const data = {
			firstName: document.querySelector('#firstName').value,
			lastName: document.querySelector('#lastName').value,
			phoneNumber: document.querySelector('#phoneNumber').value,
			email: document.querySelector('#email').value,
			userId
		}
		let jsonPayload = JSON.stringify(data)

		let url = 'api/addContact.php';
	
		let xhr = new XMLHttpRequest();
		xhr.open("POST", url, true);
		xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
		try
		{
			xhr.onreadystatechange = function() 
			{
				if (this.readyState == 4 && this.status == 200) 
				{
					console.log(`Contact added`)
				}
			};
			xhr.send(jsonPayload);
		}
		catch(err)
		{
			console.log(err.message);
		}

	})
	
}

function searchColor()
{
	let srch = document.getElementById("searchText").value;
	document.getElementById("colorSearchResult").innerHTML = "";
	
	let colorList = "";

	let tmp = {search:srch,userId:userId};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/SearchColors.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("colorSearchResult").innerHTML = "Color(s) has been retrieved";
				let jsonObject = JSON.parse( xhr.responseText );
				
				for( let i=0; i<jsonObject.results.length; i++ )
				{
					colorList += jsonObject.results[i];
					if( i < jsonObject.results.length - 1 )
					{
						colorList += "<br />\r\n";
					}
				}
				
				document.getElementsByTagName("p")[0].innerHTML = colorList;
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("colorSearchResult").innerHTML = err.message;
	}
	
}
