(function (Drupal) {
  'use strict';

  Element.prototype.hasClass = function (className) {
    return this.classList.contains(className);
  };

  Element.prototype.addClass = function (...classNames) {
    this.classList.add(...classNames);
    return this;
  };

  Element.prototype.removeClass = function (...classNames) {
    this.classList.remove(...classNames);
    return this;
  };

  Element.prototype.toggleClass = function (className, force) {
    this.classList.toggle(className, force);
    return this;
  };

  Element.prototype.replaceClass = function (oldClass, newClass) {
    this.classList.replace(oldClass, newClass);
    return this;
  };

  Element.prototype.hasAllClasses = function (...classNames) {
    return classNames.every(className =>
      this.classList.contains(className)
    );
  };

  Element.prototype.hasAnyClass = function (...classNames) {
    return classNames.some(className =>
      this.classList.contains(className)
    );
  };

})(Drupal);
