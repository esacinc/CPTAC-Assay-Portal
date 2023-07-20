$(document).ready(function() {
  var site_name = $('.page-header h3').text();
  $('.page-header h3').remove();
  $('.page-header').append('<a href="/" title="'+site_name+'">'+site_name+'</a>');
});