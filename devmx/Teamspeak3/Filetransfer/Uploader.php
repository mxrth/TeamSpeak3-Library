<?php

/*
  This file is part of TeamSpeak3 Library.

  TeamSpeak3 Library is free software: you can redistribute it and/or modify
  it under the terms of the GNU Lesser General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  TeamSpeak3 Library is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License
  along with TeamSpeak3 Library. If not, see <http://www.gnu.org/licenses/>.
 */
declare(encoding = "UTF-8");

namespace devmx\Teamspeak3\FileTransfer;

/**
 * 
 *
 * @author drak3
 */
class Uploader extends AbstractTransferer
{

    /**
     *
     * @var \devmx\Transmission\TransmissionInterface
     */
    protected $transmission;
    protected $key;
    protected $data;

    public function __construct(\devmx\Transmission\TransmissionInterface $transmission, $key, $data)
    {
        $this->transmission = $transmission;
        $this->key = $key;
    }

    public function transfer()
    {
        $bytesToSend = strlen($this->data);
        $this->transmission->establish();
        $this->sendFull($key, strlen($key));
        $this->sendFull($data, $bytesToSend);
        $this->transmission->close();
    }

}

?>
