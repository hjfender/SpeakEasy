
<html>
    <head>

    </head>
    <body>
        <div>Hello World</div>
        <?php
        $handle = fopen("Test Text File.txt", 'c+');
        $buffer = stream_get_contents($handle, $length = 512, $offset = 0);
            
        echo "<div>";
        echo $buffer; 
        echo "</div>";
        // ... change $buffer ...
        $buffer2 = "Ned is cool";
        $offset = 0;
        $bytes_written = false;
        if (0 === fseek($handle, $offset)) {
            $bytes_written = fwrite($handle, $buffer2, $length);
        }
        fclose($handle);
        ?>
    </body>
</html>