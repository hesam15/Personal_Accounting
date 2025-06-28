async function getTransactions(data) {
    let url = 'http://localhost:8000/api/transactions/costs';
    let options = {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer 1|kjpz7x34xiUdNrF3csXYJKN5P7rUFlTm4waSOofl4ec6094b',
            'Content-Type': 'application/json',
        }
    }

    try {
        const response = await fetch(url, options)
        data = await response.json();
        return data;
    }catch(err){
        console.log(err)
    }
}

let data;

getTransactions().then(response=>data=response);