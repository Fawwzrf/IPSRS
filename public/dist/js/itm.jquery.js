$(document).ready(function () {
  // code with document.ready

  // const myModalEl1 = document.getElementById("my-modal-1");
  // myModalEl1.addEventListener("hidden.bs.modal", (event) => {
  //   $("#modal-body-1").html("");
  // });

  for (let index = 1; index <= 10; index++) {
    const myModalEl = document.getElementById("my-modal-" + index);
    myModalEl.addEventListener("hidden.bs.modal", (event) => {
      $("#modal-body-" + index).html("");
    });
  }

  if (window.location.hash) {
    // @comment by alfian
    // _tab(window.location.hash.replace("#", ""));
    var navId = window.location.hash.replace("#", "");
    var hashSlash = false;
    var splitNav = '';
    if (navId.indexOf('/') > -1)
    {
      hashSlash = true;
      splitNav = navId.split("/");
      var navId = splitNav[0];
    }
    $("#tab_active_form").val(navId);
    if (hashSlash == true) {
      // @nav
      $('a[id="nav_' + navId + '"]')
      .closest("a")
      .addClass("active")
      .attr("aria-selected", "true");
      $('a[id="nav_' + navId + '"]').click();

      jqxhrAjax.abort();
      setTimeout(function() { 
        // @subnav
        if (typeof splitNav[2] === "undefined") {
          $('a[id="subnav_' + splitNav[1] + '"]').click();
        }else{
          $('a[id="subnav_'+splitNav[1]+'_' + splitNav[2] + '"]').click();
        }
      }, 500);
    } else {
      // @nav
      $('a[id="nav_' + navId + '"]')
      .closest("a")
      .addClass("active")
      .attr("aria-selected", "true");
      // @add by alfian
      $('a[id="nav_' + navId + '"]').click();
    }
  }

  var isHaveFixedBottom = document.getElementsByClassName("fixed-bottom");
  if (isHaveFixedBottom.length > 0) {
    $(".fixed-bottom").prev("div").addClass("col-before-fixed-bottom");
  } else {
    $(".fixed-bottom").prev("div").removeClass("col-before-fixed-bottom");
  }

  $(".touppercase").keyup(function () {
    this.value = this.value.toLocaleUpperCase();
  });
});
