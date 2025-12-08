<button onclick="checkout()">Checkout</button>

<script>
function checkout(){
  let form = new FormData();
  form.append("customer","Filumena");
  form.append("sku","TSHIRT01");
  form.append("qty",1);

  fetch("../api/checkout.php",{
    method:"POST",
    body:form
  }).then(res=>res.json()).then(r=>{
    alert("Resi: "+r.tracking);
  });
}
</script>
