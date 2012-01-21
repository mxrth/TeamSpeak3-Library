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


namespace devmx\Teamspeak3\FileTransfer;

/**
 * The base class for all actions done on the Teamspeak3
 * @author drak3
 */
abstract class AbstractTransferer
{

    /**
     * @var \maxessuff\Transmission\TransmissionInterface
     */
    protected abstract $transmission;

    /**
     * This function should start the transfer action
     * @return mixed
     */
    public abstract function transfer();

    /**
     * Sends given data to the transmission
     * blocks until ALL data is written
     * @param string $data the data to send
     */
    protected function sendFull($data, $bytesToSend)
    {
        while ($bytesToSend !== 0)
        {
            $bytesToSend -= $this->transmission->send($data);
        }
    }

}

?>