package com.ecommerce.cod.repository;

import com.ecommerce.cod.model.ItemPedido;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface ItemPedidoRepository extends JpaRepository<ItemPedido, Long> {

    List<ItemPedido> findByPedidoId(Long pedidoId);

    Optional<ItemPedido> findByPedidoIdAndProdutoId(Long pedidoId, Long produtoId);

    @Query("SELECT ip FROM ItemPedido ip WHERE ip.pedidoId = :pedidoId ORDER BY ip.id ASC")
    List<ItemPedido> buscarItensDoPedidoOrdenados(@Param("pedidoId") Long pedidoId);

    @Query("SELECT SUM(ip.quantidade) FROM ItemPedido ip WHERE ip.produtoId = :produtoId")
    Optional<Integer> somarQuantidadePorProduto(@Param("produtoId") Long produtoId);

    @Query("SELECT ip FROM ItemPedido ip JOIN Pedido p ON ip.pedidoId = p.id WHERE p.usuarioId = :usuarioId AND p.statusPedido = 'PENDENTE'")
    List<ItemPedido> buscarItensDePedidosPendentesDoUsuario(@Param("usuarioId") Long usuarioId);

    @Query("SELECT ip FROM ItemPedido ip WHERE ip.produtoId = :produtoId")
    List<ItemPedido> buscarTodosPorProduto(@Param("produtoId") Long produtoId);

    @Query("SELECT COUNT(ip) FROM ItemPedido ip WHERE ip.pedidoId = :pedidoId")
    Long contarItensDoPedido(@Param("pedidoId") Long pedidoId);
}
