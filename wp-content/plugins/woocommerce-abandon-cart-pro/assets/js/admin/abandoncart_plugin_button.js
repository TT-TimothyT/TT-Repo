(function() {

  tinymce.PluginManager.add('abandoncart_pro', function(editor, url) {

    var parameter = {
        type: 'menubutton',
        text: false,
        icon: "abandoncart_email_variables_pro"
      }

    var mergecode = [
      {
        text: 'Admin Phone Number',
        value: '{{admin.phone}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Cart Link',
        value: '<a href="{{cart.link}}">Cart</a>',
        onclick: function() {
          editor.insertContent(this.value());
        }
      },
      {
        text: 'Cart Total',
        value: '{{cart.total}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Checkout Link',
        value: '<a href="{{checkout.link}}">Checkout</a>',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Coupon Code',
        value: '{{coupon.code}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Cross sells',
        value: '{{cross.sells add-to-cart="Add to Cart" button-color="#999ca1" text-color="black" items="3"}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Customer e-mail address',
        value: '{{customer.email}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Customer First Name',
        value: '{{customer.firstname}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Customer Last Name',
        value: '{{customer.lastname}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Customer Full Name',
        value: '{{customer.fullname}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Customer Phone Number',
        value: '{{customer.phone}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Date when Cart was abandoned',
        value: '{{cart.abandoned_date}}',
          onclick: function() {
             editor.insertContent(this.value());
          }
      },
      {
        text: 'Product Image',
        value: '{{item.image}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Product Information/Cart Content',
        value: '{{products.cart}}',
          onclick: function() {
            editor.insertContent('<table border="0" cellspacing="5" align="center"><caption><b>Cart Details</b></caption>'
            +'<tbody>'
            +'<tr>'
            +'<th></th>'
            +'<th>Product</th>'
            +'<th>Price</th>'
            +'<th>Quantity</th>'
            +'<th>Total</th>'
            +'</tr>'
            +'<tr style="background-color:#f4f5f4;"><td>{{item.image}}</td><td>{{item.name}}</td><td>{{item.price}}</td><td>{{item.quantity}}</td><td>{{item.subtotal}}</td></tr>'
            +'<tr>'
            +'<td>&nbsp;</td>'
            +'<td>&nbsp;</td>'
            +'<td>&nbsp;</td>'
            +'<th>Cart Total:</th>'
            +'<td>{{cart.total}}</td>'
            +'</tr></tbody></table>'
            +'<br> <br>');
          }
      },
      {
        text: 'Product Name',
        value: '{{item.name}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Product Price',
        value: '{{item.price}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Product Quantity',
        value: '{{item.quantity}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Product Subtotal',
        value: '{{item.subtotal}}',
          onclick: function() {
            editor.insertContent(this.value()); 
          }
      },
      {
        text: 'Shop Name',
        value: '{{shop.name}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Shop URL',
        value: '<a href="{{shop.url}}">{{shop.name}}</a>',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Store Address',
        value: '{{store.address}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Unsubscribe Link',
        value: '<a href="{{cart.unsubscribe}}">Unsubscribe</a>',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Upsells',
        value: '{{up.sells add-to-cart="Add to Cart" button-color="#999ca1" text-color="black" items="3"}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      },
      {
        text: 'Custom Merge Tag',
        value: '{{custom-merge-tag}}',
          onclick: function() {
            editor.insertContent(this.value());
          }
      }
    ];
    parameter.menu = mergecode;
    editor.addButton('abandoncart_pro', parameter );
  });
})();