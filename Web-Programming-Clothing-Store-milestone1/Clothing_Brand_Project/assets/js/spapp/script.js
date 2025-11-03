let app = $.spapp({
    defaultView: "#home", //Defaul view that loads when u open the webpage
    templateDir: "./views/" //This is where the data-load attribute searches for the files
});

app.run();