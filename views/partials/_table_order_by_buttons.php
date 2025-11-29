<button
    type="submit"
    class="button <?php echo $orderBy === "DESC" ? "green" : "light"; ?>"
    name="order_by"
    value="newest">
    <i class="fa fa-solid fa-arrow-down"></i>
</button>

<button
    type="submit"
    class="button <?php echo $orderBy === "ASC" ? "green" : "light"; ?>"
    name="order_by"
    value="oldest">
    <i class="fa fa-solid fa-arrow-up"></i>
</button>