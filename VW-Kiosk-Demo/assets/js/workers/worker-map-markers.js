onmessage = event => {
    console.log('Worker Message:');
    console.log(event.data)
}

onerror = event => {
    console.error(event.message)
}