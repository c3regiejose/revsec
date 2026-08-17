(function (Drupal, once) {
  Drupal.behaviors.solaireSecTabs = {
    attach(context) {
      once('solaire-sec-tabs', '.tabs-component', context).forEach((tabsComponent) => {
        const buttons = tabsComponent.querySelectorAll('.tab-btn');
        const panels = tabsComponent.querySelectorAll('.panel');

        buttons.forEach((button) => {
          button.addEventListener('click', () => {
            buttons.forEach((btn) => {
              btn.classList.remove('active');
              btn.setAttribute('aria-selected', 'false');
            });

            if (button.getAttribute('data-tab') == 'All') {
              panels.forEach((panel) => panel.classList.add('active'));
              button.classList.add('active');
              button.setAttribute('aria-selected', 'true');
            } else {
              panels.forEach((panel) => panel.classList.remove('active'));

              button.classList.add('active');
              button.setAttribute('aria-selected', 'true');

              const panel = tabsComponent.querySelector('#' + CSS.escape(button.dataset.tab));
              if (panel) {
                panel.classList.add('active');
              }
            }
          });
        });
      });
    },
  };
})(Drupal, once);
