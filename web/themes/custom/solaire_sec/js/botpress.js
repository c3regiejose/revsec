((Drupal, once) => {
  Drupal.behaviors.migsMara = {
    attach(context) {
      once('migs-mara', '.migs-mara-trigger', context).forEach((button) => {
        // button.addEventListener('click', () => {
        //   console.log('ajsdhkjahsd');
        //   if (window.botpress) {
        //     window.botpress.open();
        //     window.botpress.on('webchat:ready', () => {
        //       const names = ["Migs", "Mara"];
        //       const botName = names[Math.floor(Math.random() * names.length)];
              
        //       window.botpress.sendMessage("Hi! I'm " + botName + "Welcome to San Miguel Corporation. How can I help you today?");
        //     });
        //   }
        // });

        // if (window.botpress) {
        //   // window.botpress.open();
        //   window.botpress.on('webchat:ready', () => {
        //     const names = ["Migs", "Mara"];
        //     const botName = names[Math.floor(Math.random() * names.length)];
            
        //     window.botpress.sendMessage("Hi!");
        //   });
        // }
      });
    },
  };
})(Drupal, once);
