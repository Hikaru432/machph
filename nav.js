document.addEventListener("DOMContentLoaded", function () {
  const hamBurger = document.querySelector(".toggle-btn");
  const sidebar = document.querySelector("#sidebar");

  // Check if the current page is either the home page or the profile page
  const isHomePage = window.location.pathname.includes("home.php");
  const isProfilePage = window.location.pathname.includes("profile.php");
  const isCarProfilePage = window.location.pathname.includes("carprofile.php");
  const isIdentifyPage = window.location.pathname.includes("identify.php");
  const isCarUser = window.location.pathname.includes("carusers.php");
  const isVehicleUser = window.location.pathname.includes("vehicleuser.php");
  
  // Expand the sidebar if it's the home page or the profile page
  if (isHomePage || isProfilePage || isCarProfilePage || isIdentifyPage || isCarUser || isVehicleUser) {
    sidebar.classList.add("expand");
  }

  // Toggle the sidebar when the top icon is clicked
  hamBurger.addEventListener("click", function () {
    sidebar.classList.toggle("expand");
  });

  // Close the sidebar on window resize (responsive behavior)
  window.addEventListener("resize", function () {
    if (!isHomePage && !isProfilePage) {
      sidebar.classList.remove("expand");
    }
  });
});
