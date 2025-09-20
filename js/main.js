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

let button = document.querySelector("#add-contact-button")
button.addEventListener("click", async()=> {
	readCookie();
	addContact();
})

//add contact function 
function addContact()
{
    console.log("userId:", userId); 
    
    const data = {
        firstName: document.querySelector('#firstName').value,
        lastName: document.querySelector('#lastName').value,
        phoneNumber: document.querySelector('#phoneNumber').value,
        email: document.querySelector('#email').value,
        userId
    };
    
    console.log("Data to send:", data);
    
    let jsonPayload = JSON.stringify(data);
    let url = 'api/addContact.php';
    console.log('Making API call to:', url);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    
    try {
        xhr.onreadystatechange = function() {
            console.log("ReadyState:", this.readyState, "Status:", this.status);
            
            if (this.readyState == 4) {
                console.log("Response received:", xhr.responseText);
                
                if (this.status == 200) {
                    try {
                        let response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            console.log('Contact added successfully! ID:', response.id);
                        } else if (response.error) {
                            console.log('Server error:', response.error);
                        }
                    } catch (e) {
                        console.log('Error parsing response:', e);
                    }
                } else {
                    console.log('HTTP Error - Status:', this.status);
                }
            }
        };
        xhr.send(jsonPayload);
    }
    catch(err) {
        console.log("XHR Error:", err.message);
    }
	
}
