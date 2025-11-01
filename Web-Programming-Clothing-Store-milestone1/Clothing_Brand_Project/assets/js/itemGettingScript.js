_getItems = () => {
    $.get("json/items.json", (data) => {
        let content = "";

        data.forEach(element => {
           content +=  
           `
           <div class="col-lg-3 col-md-4 col-12 ">
                <div class="single_product" id=${element.itemID}>
                    <div class="product_thumb">
                        <a class="primary_img" href="#productdetails" data-load="#productdetails"><img
                                src="${element.itemImage}" alt=""></a>
                        <a class="secondary_img" href="#productdetails" data-load="#productdetails"><img
                                src="${element.itemImage}" alt=""></a>
                        <div class="product_action">
                            <div class="hover_action">
                                <a href="#"><i class="fa fa-plus"></i></a>
                                <div class="action_button">
                                    <ul>
                                        <li><a title="add to cart" href="cart.html"><i class="fa fa-shopping-basket"
                                                    aria-hidden="true"></i></a></li>
                                        <li><a href="wishlist.html" title="Add to Wishlist"><i class="fa fa-heart-o"
                                                    aria-hidden="true"></i></a></li>
                                        <li><a href="compare.html" title="Add to Compare"><i class="fa fa-sliders"
                                                    aria-hidden="true"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="quick_button">
                            <a href="#" data-toggle="modal" data-target="#modal_box" title="quick_view">+ quick view</a>
                        </div>

                        <div class="product_sale">
                            <span>"${element.itemDiscount}"</span>
                        </div>
                    </div>

                    <div class="product_content grid_content">
                        <h3><a href="product-details.html">${element.itemName}</a></h3>
                        <span class="current_price">${element.itemNewPrice}</span>
                        <span class="old_price">${element.itemOldPrice}</span>
                    </div>


                    <div class="product_content list_content">
                        <h3><a href="product-details.html">Marshall Portable Bluetooth</a></h3>
                        <div class="product_ratting">
                            <ul>
                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                            </ul>
                        </div>
                        <div class="product_price">
                            <span class="current_price">£60.00</span>
                            <span class="old_price">£86.00</span>
                        </div>
                        <div class="product_desc">
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nobis ad, iure incidunt. Ab consequatur
                                temporibus non eveniet inventore doloremque necessitatibus sed, ducimus quisquam, ad asperiores eligendi
                                quia fugiat minus doloribus distinctio assumenda pariatur, quidem laborum quae quasi suscipit.
                                Cupiditate dolor blanditiis rerum aliquid temporibus, libero minus nihil, veniam suscipit? Autem
                                repellendus illo, amet praesentium fugit, velit natus? Dolorum perferendis reiciendis in quam porro
                                ratione eveniet, tempora saepe ducimus, alias?</p>
                        </div>

                    </div>

                </div>
            </div>
           `
        });

        document.getElementById("shopContent").innerHTML = content;
    })
};

getItems = () => {
    setTimeout((_getItems), 15)
}

$(document).on("click", ".single_product", function(){
    const itemId = $(this).attr("id");
    console.log(itemId)

    setTimeout(function(){
        $.getJSON("json/items.json", (items) => {
            const selectedItem = items.find(item => item.itemID === parseInt(itemId));
            if(selectedItem){
                $("#itemDescriptionBody").html(
                    `
                    <div class="col-lg-5 col-md-5">
                        <div class="product-details-tab">
    
                            <div id="img-1" class="zoomWrapper single-zoom">
                                <a href="#">
                                    <img id="zoom1" src=${selectedItem.itemImage} data-zoom-image="assets/img/product/product5.jpg" alt="big-1">
                                </a>
                            </div>
    
                            
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-7">
                        <div class="product_d_right">
                            <form action="#">
                                
                                <h1>${selectedItem.itemName}</h1>
                                <div class=" product_ratting">
                                    <ul>
                                        <li><a href="#"><i class="fa fa-star"></i></a></li>
                                        <li><a href="#"><i class="fa fa-star"></i></a></li>
                                        <li><a href="#"><i class="fa fa-star"></i></a></li>
                                        <li><a href="#"><i class="fa fa-star"></i></a></li>
                                        <li><a href="#"><i class="fa fa-star"></i></a></li>
                                        <li class="review"><a href="#"> 1 review </a></li>
                                        <li class="review"><a href="#"> Write a review </a></li>
                                    </ul>
                                </div>
                                <div class="product_price">
                                    <span class="current_price">${selectedItem.itemNewPrice}</span>
                                </div>
                                <div class="product_desc">
                                    <p>More room to move. With 80GB or 160GB of storage and up to 40 hours of battery life, the new iPod classic lets you enjoy up to 40,000 songs or up to 200 hours of video or any combination wherever you go. Cover Flow. Browse through your music collection by flipping through album art. Select an album to turn it over and see the track list. Enhanced interface. Experience a whole new way to browse and view your music and video. Sleeker design. Beautiful, durable, and sleeker than ever, iPod classic now features an anodized aluminum and polish.. </p>
                                </div>
    
                                <div class="product_variant color">
                                    <h3>color</h3>
                                    <select class="niceselect_option" id="color" name="produc_color">
                                        <option selected value="1">choose in option</option>        
                                        <option value="2">choose in option2</option>              
                                        <option value="3">choose in option3</option>              
                                        <option value="4">choose in option4</option>              
                                    </select>   
                                </div>
                                <div class="product_variant size">
                                    <h3>size</h3>
                                    <select class="niceselect_option" id="color1" name="produc_color">
                                        <option selected value="1">size</option>        
                                        <option value="2">x</option>              
                                        <option value="2">xl</option>              
                                        <option value="3">md</option>              
                                        <option value="4">xxl</option>              
                                        <option value="4">s</option>              
                                    </select> 
                                </div>
                                <div class="product_variant quantity">
                                    <label>quantity</label>
                                    <input min="1" max="100" value="1" type="number">
                                    <button class="button" type="submit">add to cart</button>  
                                </div>
                                <div class=" product_d_action">
                                    <ul>
                                        <li><a href="#" title="Add to wishlist"><i class="fa fa-heart-o" aria-hidden="true"></i> Add to Wish List</a></li>
                                        <li><a href="#" title="Add to Compare"><i class="fa fa-sliders" aria-hidden="true"></i> Compare this Product</a></li>
                                    </ul>
                                </div>
                                
                            </form>
                            <div class="priduct_social">
                                <h3>Share on:</h3>
                                <ul>
                                    <li><a href="#"><i class="fa fa-rss"></i></a></li>           
                                    <li><a href="#"><i class="fa fa-vimeo"></i></a></li>           
                                    <li><a href="#"><i class="fa fa-tumblr"></i></a></li>           
                                    <li><a href="#"><i class="fa fa-pinterest"></i></a></li>        
                                    <li><a href="#"><i class="fa fa-linkedin"></i></a></li>        
                                </ul>      
                            </div>
    
                        </div>
                    </div>
                    `
                );
            } else {
                console.log("ITEM NOT FOUND");
            }
        })
    }, 20)
    
})

